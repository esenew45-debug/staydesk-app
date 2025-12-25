<?php
/**
 * REST API endpoints for chatbot and external integrations.
 *
 * @since      1.0.0
 * @package    Staydesk
 */
class Staydesk_API {

    /**
     * Register REST API routes.
     */
    public function register_routes() {
        register_rest_route('staydesk/v1', '/hotels', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_hotels'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('staydesk/v1', '/hotel/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_hotel'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('staydesk/v1', '/rooms', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_rooms'),
            'permission_callback' => '__return_true'
        ));

        // New endpoint: Get hotel rooms with real-time availability
        register_rest_route('staydesk/v1', '/hotel/(?P<id>\d+)/rooms', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_hotel_rooms_realtime'),
            'permission_callback' => '__return_true'
        ));

        // New endpoint: Check room availability for specific dates
        register_rest_route('staydesk/v1', '/hotel/(?P<id>\d+)/availability', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_availability'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('staydesk/v1', '/bookings', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_booking'),
            'permission_callback' => array($this, 'verify_api_key')
        ));

        register_rest_route('staydesk/v1', '/chatbot', array(
            'methods' => 'POST',
            'callback' => array($this, 'chatbot_endpoint'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('staydesk/v1', '/widget-config/(?P<hotel_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_widget_config'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Get all hotels.
     */
    public function get_hotels($request) {
        global $wpdb;

        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotels = $wpdb->get_results(
            "SELECT id, hotel_name, hotel_email, hotel_phone, hotel_address, hotel_description 
             FROM $table_hotels 
             WHERE subscription_status = 'active' 
             AND email_confirmed = 1"
        );

        return rest_ensure_response(array(
            'success' => true,
            'data' => $hotels
        ));
    }

    /**
     * Get single hotel.
     */
    public function get_hotel($request) {
        global $wpdb;

        $hotel_id = $request['id'];

        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_hotels WHERE id = %d",
            $hotel_id
        ));

        if (!$hotel) {
            return new WP_Error('not_found', 'Hotel not found', array('status' => 404));
        }

        // Get hotel rooms
        $rooms = Staydesk_Rooms::get_hotel_rooms($hotel_id);

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'hotel' => $hotel,
                'rooms' => $rooms
            )
        ));
    }

    /**
     * Get rooms.
     */
    public function get_rooms($request) {
        global $wpdb;

        $hotel_id = isset($request['hotel_id']) ? intval($request['hotel_id']) : 0;

        $table_rooms = $wpdb->prefix . 'staydesk_rooms';
        
        if ($hotel_id > 0) {
            $rooms = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_rooms WHERE hotel_id = %d AND availability_status = 'available'",
                $hotel_id
            ));
        } else {
            $rooms = $wpdb->get_results(
                "SELECT * FROM $table_rooms WHERE availability_status = 'available'"
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => $rooms
        ));
    }

    /**
     * Create booking via API.
     */
    public function create_booking($request) {
        global $wpdb;

        $params = $request->get_json_params();

        // Validate required fields
        $required = array('room_id', 'check_in_date', 'check_out_date', 'guest_name', 'guest_email', 'guest_phone');
        foreach ($required as $field) {
            if (empty($params[$field])) {
                return new WP_Error('missing_field', "Field {$field} is required", array('status' => 400));
            }
        }

        // Create guest
        $table_guests = $wpdb->prefix . 'staydesk_guests';
        $wpdb->insert($table_guests, array(
            'guest_name' => sanitize_text_field($params['guest_name']),
            'guest_email' => sanitize_email($params['guest_email']),
            'guest_phone' => sanitize_text_field($params['guest_phone'])
        ));
        $guest_id = $wpdb->insert_id;

        // Get room details
        $table_rooms = $wpdb->prefix . 'staydesk_rooms';
        $room = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_rooms WHERE id = %d",
            intval($params['room_id'])
        ));

        if (!$room) {
            return new WP_Error('not_found', 'Room not found', array('status' => 404));
        }

        // Calculate total
        $check_in = new DateTime($params['check_in_date']);
        $check_out = new DateTime($params['check_out_date']);
        $nights = $check_in->diff($check_out)->days;
        $total = $room->price_per_night * $nights;

        // Create booking
        $reference = 'BK' . strtoupper(substr(uniqid(), -8));
        
        $table_bookings = $wpdb->prefix . 'staydesk_bookings';
        $wpdb->insert($table_bookings, array(
            'booking_reference' => $reference,
            'hotel_id' => $room->hotel_id,
            'room_id' => $room->id,
            'guest_id' => $guest_id,
            'check_in_date' => $params['check_in_date'],
            'check_out_date' => $params['check_out_date'],
            'num_guests' => isset($params['num_guests']) ? intval($params['num_guests']) : 1,
            'total_amount' => $total,
            'booking_status' => 'pending',
            'payment_status' => 'pending'
        ));

        $booking_id = $wpdb->insert_id;

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => array(
                'booking_id' => $booking_id,
                'booking_reference' => $reference,
                'total_amount' => $total
            )
        ));
    }

    /**
     * Chatbot API endpoint.
     */
    public function chatbot_endpoint($request) {
        $params = $request->get_json_params();

        $hotel_id = isset($params['hotel_id']) ? intval($params['hotel_id']) : 0;
        $session_id = isset($params['session_id']) ? sanitize_text_field($params['session_id']) : 'session_' . uniqid();
        $message = isset($params['message']) ? sanitize_text_field($params['message']) : '';
        $language = isset($params['language']) ? sanitize_text_field($params['language']) : 'en';

        if (empty($message)) {
            return new WP_Error('missing_message', 'Message is required', array('status' => 400));
        }

        // Use chatbot class to process
        $chatbot = new Staydesk_Chatbot();
        
        // Simulate AJAX post data
        $_POST['hotel_id'] = $hotel_id;
        $_POST['session_id'] = $session_id;
        $_POST['message'] = $message;
        $_POST['language'] = $language;

        // Process through chatbot (but we need to return directly)
        // For API, we'll create a direct method
        
        return rest_ensure_response(array(
            'success' => true,
            'session_id' => $session_id,
            'response' => 'This endpoint processes chatbot messages'
        ));
    }

    /**
     * Get widget configuration for a hotel.
     */
    public function get_widget_config($request) {
        global $wpdb;

        $hotel_id = $request['hotel_id'];

        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT id, hotel_name, subscription_status FROM $table_hotels WHERE id = %d",
            $hotel_id
        ));

        if (!$hotel) {
            return new WP_Error('not_found', 'Hotel not found', array('status' => 404));
        }

        $config = array(
            'hotel_id' => $hotel->id,
            'hotel_name' => $hotel->hotel_name,
            'enabled' => $hotel->subscription_status === 'active',
            'api_url' => rest_url('staydesk/v1/chatbot'),
            'languages' => array('en', 'pidgin'),
            'whatsapp_fallback' => '2347120018023'
        );

        return rest_ensure_response(array(
            'success' => true,
            'data' => $config
        ));
    }

    /**
     * Verify API key.
     */
    public function verify_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        
        if (empty($api_key)) {
            return false;
        }

        global $wpdb;
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        
        // Check if API key exists in any hotel record
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_hotels WHERE api_key = %s AND subscription_status = 'active'",
            $api_key
        ));
        
        return !empty($hotel);
    }

    /**
     * Get hotel rooms with real-time availability.
     */
    public function get_hotel_rooms_realtime($request) {
        global $wpdb;

        $hotel_id = $request['id'];

        // Verify hotel exists
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_hotels WHERE id = %d",
            $hotel_id
        ));

        if (!$hotel) {
            return new WP_Error('not_found', 'Hotel not found', array('status' => 404));
        }

        // Get rooms with real-time availability
        $table_rooms = $wpdb->prefix . 'staydesk_rooms';
        $rooms = $wpdb->get_results($wpdb->prepare(
            "SELECT id, room_name, room_type, room_description, price_per_night, 
                    max_guests, amenities, availability_status, room_image
             FROM $table_rooms 
             WHERE hotel_id = %d 
             ORDER BY price_per_night ASC",
            $hotel_id
        ));

        // Format amenities as array
        foreach ($rooms as &$room) {
            $room->amenities = !empty($room->amenities) ? array_map('trim', explode(',', $room->amenities)) : array();
            $room->price_formatted = '₦' . number_format($room->price_per_night, 0);
            $room->is_available = $room->availability_status === 'available';
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'hotel' => array(
                    'id' => $hotel->id,
                    'name' => $hotel->hotel_name,
                    'description' => $hotel->hotel_description,
                    'address' => $hotel->hotel_address,
                    'phone' => $hotel->hotel_phone,
                    'email' => $hotel->hotel_email
                ),
                'rooms' => $rooms,
                'total_rooms' => count($rooms),
                'available_rooms' => count(array_filter($rooms, function($r) { return $r->is_available; })),
                'updated_at' => current_time('c')
            )
        ));
    }

    /**
     * Check room availability for specific dates.
     */
    public function check_availability($request) {
        global $wpdb;

        $hotel_id = $request['id'];
        $check_in = isset($request['check_in']) ? sanitize_text_field($request['check_in']) : '';
        $check_out = isset($request['check_out']) ? sanitize_text_field($request['check_out']) : '';
        $guests = isset($request['guests']) ? intval($request['guests']) : 1;
        $room_type = isset($request['room_type']) ? sanitize_text_field($request['room_type']) : '';

        // Verify hotel exists
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_hotels WHERE id = %d",
            $hotel_id
        ));

        if (!$hotel) {
            return new WP_Error('not_found', 'Hotel not found', array('status' => 404));
        }

        // Build query for rooms
        $table_rooms = $wpdb->prefix . 'staydesk_rooms';
        $table_bookings = $wpdb->prefix . 'staydesk_bookings';

        $where_clauses = array("r.hotel_id = %d", "r.availability_status = 'available'");
        $where_values = array($hotel_id);

        if ($guests > 0) {
            $where_clauses[] = "r.max_guests >= %d";
            $where_values[] = $guests;
        }

        if (!empty($room_type)) {
            $where_clauses[] = "r.room_type = %s";
            $where_values[] = $room_type;
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Get available rooms
        $query = "SELECT r.* FROM $table_rooms r WHERE $where_sql";
        $rooms = $wpdb->get_results($wpdb->prepare($query, ...$where_values));

        // If dates provided, filter by existing bookings
        if (!empty($check_in) && !empty($check_out)) {
            $available_rooms = array();
            
            foreach ($rooms as $room) {
                // Check if room has conflicting bookings
                // Two date ranges overlap if: start1 < end2 AND end1 > start2
                $conflict = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_bookings 
                     WHERE room_id = %d 
                     AND booking_status NOT IN ('cancelled', 'rejected')
                     AND check_in_date < %s 
                     AND check_out_date > %s",
                    $room->id,
                    $check_out,
                    $check_in
                ));

                if ($conflict == 0) {
                    // Calculate nights and total
                    $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
                    $room->nights = max(1, intval($nights)); // Ensure at least 1 night
                    $room->total_price = $room->price_per_night * $room->nights;
                    $room->total_price_formatted = '₦' . number_format($room->total_price, 0);
                    $room->price_formatted = '₦' . number_format($room->price_per_night, 0);
                    $room->is_available = true;
                    $room->amenities = !empty($room->amenities) ? array_map('trim', explode(',', $room->amenities)) : array();
                    $available_rooms[] = $room;
                }
            }
            
            $rooms = $available_rooms;
        } else {
            // Format rooms without date calculation
            foreach ($rooms as &$room) {
                $room->price_formatted = '₦' . number_format($room->price_per_night, 0);
                $room->is_available = true;
                $room->amenities = !empty($room->amenities) ? array_map('trim', explode(',', $room->amenities)) : array();
            }
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'hotel_id' => $hotel_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'guests' => $guests,
                'room_type' => $room_type,
                'available_rooms' => $rooms,
                'total_available' => count($rooms),
                'updated_at' => current_time('c')
            )
        ));
    }
}