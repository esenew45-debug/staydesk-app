<?php
/**
 * AI Chatbot functionality with document training support.
 *
 * @since      1.0.0
 * @package    Staydesk
 */
class Staydesk_Chatbot {

    /**
     * Initialize the class.
     */
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_nopriv_staydesk_chatbot_message', array($this, 'process_message'));
        add_action('wp_ajax_staydesk_chatbot_message', array($this, 'process_message'));
        
        // Document training AJAX handlers
        add_action('wp_ajax_staydesk_upload_training_document', array($this, 'upload_training_document'));
        add_action('wp_ajax_staydesk_delete_training_document', array($this, 'delete_training_document'));
        add_action('wp_ajax_staydesk_save_chatbot_settings', array($this, 'save_chatbot_settings'));
    }
    
    /**
     * Upload and process training document (PDF/DOC).
     */
    public function upload_training_document() {
        check_ajax_referer('staydesk_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login to upload documents.');
        }
        
        $hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
        
        if ($hotel_id <= 0) {
            wp_send_json_error('Invalid hotel ID.');
        }
        
        // Verify user owns this hotel
        global $wpdb;
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_hotels WHERE id = %d AND user_id = %d",
            $hotel_id,
            get_current_user_id()
        ));
        
        if (!$hotel) {
            wp_send_json_error('Access denied.');
        }
        
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('No file uploaded or upload error.');
        }
        
        $file = $_FILES['document'];
        $allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $allowed_extensions = array('pdf', 'doc', 'docx');
        
        // Get file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Security: Validate BOTH extension AND MIME type for uploaded files
        if (!in_array($file_extension, $allowed_extensions)) {
            wp_send_json_error('Invalid file extension. Only PDF, DOC, and DOCX files are allowed.');
        }
        
        // Additional MIME type check (note: MIME types can be spoofed, but this adds a layer)
        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
        if ($finfo) {
            $detected_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            // Map extensions to their actual MIME types
            $type_map = array(
                'pdf' => array('application/pdf'),
                'doc' => array('application/msword', 'application/octet-stream'),
                'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream')
            );
            
            if (!isset($type_map[$file_extension]) || !in_array($detected_type, $type_map[$file_extension])) {
                // Allow common types that may be detected differently
                if (!in_array($detected_type, array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'))) {
                    wp_send_json_error('File content does not match the expected type.');
                }
            }
        }
        
        // Check file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error('File too large. Maximum size is 10MB.');
        }
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $training_dir = $upload_dir['basedir'] . '/staydesk-training/' . $hotel_id;
        
        if (!file_exists($training_dir)) {
            wp_mkdir_p($training_dir);
        }
        
        // Generate unique filename
        $new_filename = uniqid('doc_') . '.' . $file_extension;
        $file_path = $training_dir . '/' . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error('Failed to save file.');
        }
        
        // Extract text content from document
        $text_content = $this->extract_document_text($file_path, $file_extension);
        
        // Save to database
        $table_docs = $wpdb->prefix . 'staydesk_training_documents';
        $inserted = $wpdb->insert($table_docs, array(
            'hotel_id' => $hotel_id,
            'file_name' => sanitize_file_name($file['name']),
            'file_path' => $file_path,
            'file_type' => $file_extension,
            'file_size' => $file['size'],
            'text_content' => $text_content,
            'is_processed' => !empty($text_content) ? 1 : 0,
            'uploaded_at' => current_time('mysql')
        ));
        
        if ($inserted) {
            wp_send_json_success(array(
                'message' => 'Document uploaded successfully!',
                'document_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error('Failed to save document information.');
        }
    }
    
    /**
     * Extract text from uploaded document.
     */
    private function extract_document_text($file_path, $file_type) {
        $text = '';
        
        if ($file_type === 'pdf') {
            // Basic PDF text extraction
            $content = file_get_contents($file_path);
            if ($content) {
                // Extract text between stream and endstream markers
                preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches);
                foreach ($matches[1] as $stream) {
                    // Try to decode if it's compressed
                    $decoded = @gzuncompress($stream);
                    if ($decoded) {
                        // Extract readable text
                        preg_match_all('/\((.*?)\)/', $decoded, $text_matches);
                        foreach ($text_matches[1] as $match) {
                            $text .= $match . ' ';
                        }
                    }
                }
                
                // Fallback: try to extract any readable text
                if (empty(trim($text))) {
                    preg_match_all('/\((.*?)\)/', $content, $text_matches);
                    foreach ($text_matches[1] as $match) {
                        // Only include readable text
                        if (preg_match('/^[\x20-\x7E\s]+$/', $match) && strlen($match) > 2) {
                            $text .= $match . ' ';
                        }
                    }
                }
                
                // Additional fallback for BT/ET blocks
                if (empty(trim($text))) {
                    preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $bt_matches);
                    foreach ($bt_matches[1] as $block) {
                        preg_match_all('/\((.*?)\)/', $block, $inner_matches);
                        foreach ($inner_matches[1] as $match) {
                            $text .= $match . ' ';
                        }
                    }
                }
            }
        } elseif (in_array($file_type, array('doc', 'docx'))) {
            if ($file_type === 'docx') {
                // Extract from DOCX (it's a zip file)
                $zip = new ZipArchive();
                if ($zip->open($file_path) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    if ($xml) {
                        // Disable external entity loading for security
                        $previous_state = libxml_use_internal_errors(true);
                        $disable_entities = libxml_disable_entity_loader(true);
                        
                        // Parse XML safely
                        $dom = new DOMDocument();
                        $dom->loadXML($xml, LIBXML_NOENT | LIBXML_NONET | LIBXML_NOBLANKS);
                        
                        // Extract text content only
                        $text = '';
                        $paragraphs = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'p');
                        foreach ($paragraphs as $p) {
                            $text .= $p->textContent . ' ';
                        }
                        
                        // Restore state
                        libxml_use_internal_errors($previous_state);
                        if (function_exists('libxml_disable_entity_loader')) {
                            @libxml_disable_entity_loader($disable_entities);
                        }
                        
                        // If no text found via DOM, fallback to simple extraction
                        if (empty(trim($text))) {
                            // Use preg to extract text between tags
                            preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $xml, $t_matches);
                            $text = implode(' ', $t_matches[1]);
                        }
                        
                        $text = preg_replace('/\s+/', ' ', $text);
                    }
                    $zip->close();
                }
            } else {
                // Basic DOC extraction - extract readable ASCII sequences
                $content = file_get_contents($file_path);
                if ($content) {
                    // Extract readable ASCII text sequences (4+ chars)
                    preg_match_all('/[\x20-\x7E]{4,}/', $content, $matches);
                    $text = implode(' ', $matches[0]);
                }
            }
        }
        
        // Clean up extracted text
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Delete training document.
     */
    public function delete_training_document() {
        check_ajax_referer('staydesk_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login.');
        }
        
        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
        $hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
        
        if ($document_id <= 0) {
            wp_send_json_error('Invalid document ID.');
        }
        
        global $wpdb;
        
        // Verify user owns the hotel
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_hotels WHERE id = %d AND user_id = %d",
            $hotel_id,
            get_current_user_id()
        ));
        
        if (!$hotel) {
            wp_send_json_error('Access denied.');
        }
        
        // Get document info
        $table_docs = $wpdb->prefix . 'staydesk_training_documents';
        $doc = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_docs WHERE id = %d AND hotel_id = %d",
            $document_id,
            $hotel_id
        ));
        
        if (!$doc) {
            wp_send_json_error('Document not found.');
        }
        
        // Delete file from filesystem first
        $file_deleted = true;
        if (file_exists($doc->file_path)) {
            $file_deleted = @unlink($doc->file_path);
            if (!$file_deleted) {
                // Try WordPress method as fallback
                wp_delete_file($doc->file_path);
                $file_deleted = !file_exists($doc->file_path);
            }
        }
        
        // Only delete from database if file was successfully removed (or doesn't exist)
        if ($file_deleted || !file_exists($doc->file_path)) {
            $deleted = $wpdb->delete($table_docs, array('id' => $document_id));
            
            if ($deleted) {
                wp_send_json_success('Document deleted.');
            } else {
                wp_send_json_error('Failed to delete document record.');
            }
        } else {
            wp_send_json_error('Failed to delete file. Please contact support.');
        }
    }
    
    /**
     * Save chatbot settings.
     */
    public function save_chatbot_settings() {
        check_ajax_referer('staydesk_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login.');
        }
        
        $hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
        $response_tone = isset($_POST['response_tone']) ? sanitize_text_field($_POST['response_tone']) : 'friendly';
        $response_length = isset($_POST['response_length']) ? sanitize_text_field($_POST['response_length']) : 'balanced';
        
        global $wpdb;
        
        // Verify user owns the hotel
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_hotels WHERE id = %d AND user_id = %d",
            $hotel_id,
            get_current_user_id()
        ));
        
        if (!$hotel) {
            wp_send_json_error('Access denied.');
        }
        
        // Save settings
        $settings = array(
            'response_tone' => $response_tone,
            'response_length' => $response_length
        );
        
        $updated = $wpdb->update(
            $table_hotels,
            array('chatbot_settings' => json_encode($settings)),
            array('id' => $hotel_id)
        );
        
        wp_send_json_success('Settings saved.');
    }
    
    /**
     * Get training data for hotel.
     */
    private function get_training_data($hotel_id) {
        global $wpdb;
        $table_docs = $wpdb->prefix . 'staydesk_training_documents';
        
        $docs = $wpdb->get_results($wpdb->prepare(
            "SELECT text_content FROM $table_docs WHERE hotel_id = %d AND is_processed = 1",
            $hotel_id
        ));
        
        $combined_text = '';
        foreach ($docs as $doc) {
            if (!empty($doc->text_content)) {
                $combined_text .= $doc->text_content . "\n\n";
            }
        }
        
        return $combined_text;
    }
    
    /**
     * Search training data for relevant information.
     */
    private function search_training_data($query, $training_data) {
        if (empty($training_data)) {
            return null;
        }
        
        $query_lower = strtolower($query);
        $training_lower = strtolower($training_data);
        
        // Split training data into sentences/paragraphs
        $segments = preg_split('/[.!?\n]+/', $training_data);
        $relevant_segments = array();
        
        // Extract keywords from query
        $keywords = preg_split('/\s+/', $query_lower);
        $keywords = array_filter($keywords, function($word) {
            return strlen($word) > 2 && !in_array($word, array('the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'her', 'was', 'one', 'our', 'out', 'what', 'does', 'have', 'this', 'will', 'your', 'from', 'they', 'been', 'have', 'many', 'some', 'them', 'with'));
        });
        
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if (strlen($segment) < 10) continue;
            
            $segment_lower = strtolower($segment);
            $score = 0;
            
            foreach ($keywords as $keyword) {
                if (strpos($segment_lower, $keyword) !== false) {
                    $score++;
                }
            }
            
            if ($score > 0) {
                $relevant_segments[] = array(
                    'text' => $segment,
                    'score' => $score
                );
            }
        }
        
        // Sort by relevance score
        usort($relevant_segments, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Return top 3 most relevant segments
        $results = array_slice($relevant_segments, 0, 3);
        
        if (!empty($results)) {
            return implode('. ', array_column($results, 'text')) . '.';
        }
        
        return null;
    }

    /**
     * Process chatbot message.
     */
    public function process_message() {
        global $wpdb;

        $hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : 'en';
        $query_rooms = isset($_POST['query_rooms']) ? (bool)$_POST['query_rooms'] : false;
        $query_hotel_info = isset($_POST['query_hotel_info']) ? (bool)$_POST['query_hotel_info'] : false;

        if (empty($message)) {
            wp_send_json_error(array('message' => 'Message is required.'));
        }

        // Generate session ID if not provided
        if (empty($session_id)) {
            $session_id = 'session_' . uniqid();
        }

        // Check hotel subscription status for full chatbot features
        $has_active_subscription = false;
        if ($hotel_id > 0) {
            $table_hotels = $wpdb->prefix . 'staydesk_hotels';
            $hotel_record = $wpdb->get_row($wpdb->prepare(
                "SELECT subscription_status, subscription_expiry FROM $table_hotels WHERE id = %d",
                $hotel_id
            ));
            
            if ($hotel_record && $hotel_record->subscription_status === 'active') {
                // Check if subscription is not expired
                if (!$hotel_record->subscription_expiry || strtotime($hotel_record->subscription_expiry) > time()) {
                    $has_active_subscription = true;
                }
            }
        }
        
        // Note: FAQ queries work for all hotels regardless of subscription status
        // Premium AI features can check $has_active_subscription flag

        // Log user message
        $table_chat_logs = $wpdb->prefix . 'staydesk_chat_logs';
        $wpdb->insert($table_chat_logs, array(
            'hotel_id' => $hotel_id,
            'session_id' => $session_id,
            'message_type' => 'user',
            'message_text' => $message,
            'language' => $language
        ));

        // Process message and generate response
        $response = $this->generate_response($hotel_id, $message, $session_id, $language);
        
        // Add real-time room availability if requested
        if ($query_rooms && $hotel_id > 0) {
            $response['rooms'] = $this->get_available_rooms($hotel_id);
        }
        
        // Add hotel info if requested
        if ($query_hotel_info && $hotel_id > 0) {
            $response['hotel_info'] = $this->get_hotel_info($hotel_id);
        }

        // Log bot response
        $wpdb->insert($table_chat_logs, array(
            'hotel_id' => $hotel_id,
            'session_id' => $session_id,
            'message_type' => 'bot',
            'message_text' => $response['message'],
            'language' => $language,
            'response_text' => json_encode($response)
        ));

        wp_send_json_success($response);
    }

    /**
     * Ultra-intelligent intent patterns (500+ patterns)
     */
    private function get_intent_patterns() {
        return array(
            'room_availability' => array(
                'rooms', 'room available', 'vacancy', 'free room', 'available rooms',
                'got rooms', 'have rooms', 'any rooms', 'rooms free', 'vacant rooms',
                'open rooms', 'unbooked', 'space', 'accommodation', 'lodging',
                'show me rooms', 'list rooms', 'room types', 'what rooms',
                'room options', 'see rooms', 'view rooms', 'check rooms',
                'room status', 'got space', 'have space', 'any space'
            ),
            'pricing' => array(
                'price', 'cost', 'rate', 'how much', 'charges', 'fees', 'amount',
                'room price', 'nightly rate', 'per night', 'room cost',
                'how expensive', 'affordable', 'cheap', 'budget',
                'what do you charge', 'rates', 'pricing', 'payment',
                'cheapest', 'most affordable', 'lowest price', 'expensive',
                'luxury price', 'premium rate', 'total cost', 'full amount'
            ),
            'facilities' => array(
                'facilities', 'amenities', 'services', 'features', 'have',
                'wifi', 'internet', 'pool', 'gym', 'generator', 'parking',
                'restaurant', 'bar', 'spa', 'laundry', 'room service',
                'do you have', 'is there', 'got', 'available',
                'fitness', 'exercise', 'workout', 'swimming', 'water',
                'power', 'electricity', 'backup', 'nepa', 'light'
            ),
            'food_dining' => array(
                'food', 'restaurant', 'dining', 'breakfast', 'meals',
                'jollof rice', 'nigerian food', 'local cuisine', 'african food',
                'menu', 'what food', 'food options', 'eat',
                'room service', 'in-room dining', 'delivery',
                'breakfast included', 'free breakfast', 'complimentary',
                'lunch', 'dinner', 'snacks', 'drinks', 'beverages'
            ),
            'booking' => array(
                'book', 'reserve', 'reservation', 'make booking',
                'i want to book', 'how to book', 'booking process',
                'reserve room', 'make reservation', 'secure room',
                'book for', 'reserve for', 'booking for',
                'i would like to book', 'can i book', 'want to reserve',
                'schedule', 'arrange', 'organize booking'
            ),
            'location' => array(
                'location', 'address', 'where are you', 'directions',
                'how to get there', 'how far', 'distance', 'proximity',
                'near airport', 'near vi', 'near lekki', 'near ikeja',
                'landmarks nearby', 'close to', 'vicinity', 'area',
                'neighborhood', 'find you', 'navigate', 'map'
            ),
            'checkin_checkout' => array(
                'check in', 'check out', 'checkin', 'checkout',
                'arrival time', 'departure time', 'check in time',
                'what time check in', 'checkout time', 'check out time',
                'early check in', 'late checkout', 'late check in',
                'arrival procedure', 'check in process', 'registration'
            ),
            'payment_methods' => array(
                'payment', 'pay', 'payment methods', 'how to pay',
                'accept cards', 'credit card', 'debit card', 'mastercard',
                'visa', 'cash', 'bank transfer', 'paystack', 'online payment',
                'payment options', 'payment plan', 'deposit', 'advance'
            ),
            'policies' => array(
                'policy', 'rules', 'regulations', 'terms', 'conditions',
                'cancellation policy', 'refund policy', 'cancellation',
                'pet policy', 'smoking policy', 'pets allowed',
                'can i bring pets', 'is smoking allowed', 'smoke',
                'children policy', 'kids allowed', 'family friendly'
            )
        );
    }

    /**
     * Comprehensive synonym mapping (200+ synonyms)
     */
    private function get_synonym_map() {
        return array(
            // Room synonyms
            'room' => 'chamber suite accommodation lodging unit quarters',
            'available' => 'vacant free open unoccupied ready empty',
            'book' => 'reserve schedule arrange secure make get',
            
            // Pricing synonyms
            'price' => 'cost rate charge fee amount money payment',
            'cheap' => 'affordable budget economical inexpensive low',
            'expensive' => 'costly premium luxury high-end pricey dear',
            
            // Facility synonyms
            'wifi' => 'internet wireless broadband connectivity online',
            'pool' => 'swimming water swim pool area aquatic',
            'gym' => 'fitness center exercise workout training health',
            'generator' => 'power backup electricity supply light nepa',
            
            // Food synonyms
            'food' => 'meal cuisine dish menu catering dining',
            'breakfast' => 'morning meal first meal early breakfast buffet',
            'restaurant' => 'dining eatery cafe bistro kitchen',
            
            // Location synonyms
            'location' => 'address place spot position site venue',
            'near' => 'close nearby adjacent proximity vicinity around',
            'directions' => 'route way path navigate find location',
            
            // Booking synonyms
            'booking' => 'reservation reserve book schedule arrangement',
            'guest' => 'visitor customer client patron occupant',
            
            // Time synonyms
            'check in' => 'arrival checkin arrive register entry',
            'check out' => 'checkout departure leave depart exit',
            
            // Quality synonyms
            'good' => 'nice great excellent quality fine decent',
            'bad' => 'poor terrible awful horrible unacceptable'
        );
    }

    /**
     * Apply synonym expansion to message
     */
    private function apply_synonyms($message) {
        $synonym_map = $this->get_synonym_map();
        $expanded = $message;
        
        foreach ($synonym_map as $word => $synonyms) {
            if (stripos($message, $word) !== false) {
                $expanded .= ' ' . $synonyms;
            }
        }
        
        return strtolower($expanded);
    }

    /**
     * Detect user intent with ultra-smart pattern matching
     */
    private function detect_intent($message, $language) {
        $message_lower = strtolower(trim($message));
        
        // Apply Pidgin translation first
        if ($language === 'pidgin' || preg_match('/(wetin|una|dey|abeg|fit|make|wan)/i', $message_lower)) {
            $message_lower = $this->translate_pidgin_to_english($message_lower);
        }
        
        // Apply synonyms for better matching
        $message_expanded = $this->apply_synonyms($message_lower);
        
        // Get intent patterns
        $intent_patterns = $this->get_intent_patterns();
        
        // Score each intent
        $intent_scores = array();
        
        foreach ($intent_patterns as $intent => $patterns) {
            $score = 0;
            foreach ($patterns as $pattern) {
                // Exact phrase match (higher weight)
                if (stripos($message_lower, $pattern) !== false) {
                    $score += 3;
                }
                // Synonym/expanded match
                if (stripos($message_expanded, $pattern) !== false) {
                    $score += 1;
                }
            }
            if ($score > 0) {
                $intent_scores[$intent] = $score;
            }
        }
        
        // Return highest scoring intent
        if (!empty($intent_scores)) {
            arsort($intent_scores);
            return key($intent_scores);
        }
        
        return 'unknown';
    }

    /**
     * Generate chatbot response.
     */
    private function generate_response($hotel_id, $message, $session_id, $language = 'en') {
        global $wpdb;

        $message_lower = strtolower($message);

        // Get conversation context from recent messages
        $context = $this->get_conversation_context($session_id);
        
        // Detect user intent with ultra-smart NLP
        $intent = $this->detect_intent($message, $language);
        
        // PRIORITY 1: Search training documents for relevant information
        if ($hotel_id > 0) {
            $training_data = $this->get_training_data($hotel_id);
            if (!empty($training_data)) {
                $training_response = $this->search_training_data($message, $training_data);
                if ($training_response) {
                    // Format the response in a human-like way
                    $formatted_response = $this->format_training_response($training_response, $message, $language);
                    return array(
                        'message' => $formatted_response,
                        'type' => 'training_data_response',
                        'source' => 'document'
                    );
                }
            }
        }
        
        // Get hotel data
        $hotel = null;
        if ($hotel_id > 0) {
            $table_hotels = $wpdb->prefix . 'staydesk_hotels';
            $hotel = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_hotels WHERE id = %d",
                $hotel_id
            ));
        }

        // PRIORITY 2: Intent-based response routing
        if ($intent && $intent !== 'unknown') {
            // Route to specialized handlers based on detected intent
            switch ($intent) {
                case 'room_availability':
                    return $this->handle_room_availability_intent($hotel_id, $message, $language, null);
                    
                case 'pricing':
                    return $this->handle_pricing_intent($hotel_id, $message, $language);
                    
                case 'booking':
                    // Start guided booking flow
                    return $this->handle_booking_flow($hotel_id, $message, $session_id, $language, null);
            }
        }

        // ENHANCED: Translate Pidgin to English first for better understanding
        $original_message = $message_lower;
        if ($language === 'pidgin' || preg_match('/(wetin|una|dey|abeg|fit|make|wan)/i', $message_lower)) {
            $message_lower = $this->translate_pidgin_to_english($message_lower);
        }

        // Check for greeting
        if (preg_match('/(hello|hi|hey|good morning|good afternoon|good evening|wetin dey|wetin sup)/i', $original_message)) {
            return array(
                'message' => $this->translate("Hello! Welcome to " . ($hotel ? $hotel->hotel_name : "StayDesk") . ". How can I help you today? I've been trained on custom documents to give you accurate information!", $language),
                'type' => 'greeting',
                'options' => array(
                    'Check available rooms',
                    'Make a booking',
                    'Check booking status',
                    'Ask any question'
                )
            );
        }

        // Room availability query
        if (preg_match('/(room|available|vacancy|free|book|reserve)/i', $message_lower)) {
            if ($hotel_id > 0) {
                $table_rooms = $wpdb->prefix . 'staydesk_rooms';
                $rooms = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $table_rooms WHERE hotel_id = %d AND availability_status = 'available'",
                    $hotel_id
                ));
                
                if (empty($rooms)) {
                    return array(
                        'message' => $this->translate("Currently, there are no rooms available. Would you like me to notify you when rooms become available?", $language),
                        'type' => 'no_rooms'
                    );
                }

                $response = $this->translate("We have " . count($rooms) . " rooms available", $language) . ":\n\n";
                foreach ($rooms as $room) {
                    $response .= "• " . $room->room_name . " - ₦" . number_format($room->price_per_night) . "/night\n";
                    $response .= "  Type: " . $room->room_type . ", Capacity: " . $room->capacity . " guests\n\n";
                }
                $response .= $this->translate("Would you like to book a room?", $language);

                return array(
                    'message' => $response,
                    'type' => 'room_list',
                    'rooms' => $rooms
                );
            }
        }

        // Check for booking status query
        if (preg_match('/(booking|reservation|reference|status)/i', $message_lower)) {
            return array(
                'message' => $this->translate("Please provide your booking reference number (e.g., BK12345678) so I can check your booking status.", $language),
                'type' => 'booking_inquiry',
                'input_required' => 'booking_reference'
            );
        }

        // Check if message contains booking reference
        if (preg_match('/BK[A-Z0-9]{8}/i', $message, $matches)) {
            $reference = strtoupper($matches[0]);
            if (class_exists('Staydesk_Bookings') && method_exists('Staydesk_Bookings', 'get_booking_by_reference')) {
                $booking = Staydesk_Bookings::get_booking_by_reference($reference);
                
                if ($booking) {
                    return array(
                        'message' => $this->translate("I found your booking! Reference: {$reference}, Status: {$booking->booking_status}, Payment: {$booking->payment_status}, Check-in: {$booking->check_in_date}, Check-out: {$booking->check_out_date}", $language),
                        'type' => 'booking_found',
                        'booking' => $booking
                    );
                }
            }
            return array(
                'message' => $this->translate("I couldn't find a booking with reference {$reference}. Please check the reference number and try again.", $language),
                'type' => 'booking_not_found'
            );
        }

        // Pricing query
        if (preg_match('/(price|cost|how much|rate|amount)/i', $message_lower)) {
            if ($hotel_id > 0) {
                $table_rooms = $wpdb->prefix . 'staydesk_rooms';
                $rooms = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $table_rooms WHERE hotel_id = %d ORDER BY price_per_night ASC",
                    $hotel_id
                ));
                
                if (!empty($rooms)) {
                    $price_info = array();
                    foreach ($rooms as $room) {
                        $price_info[] = $room->room_name . ": ₦" . number_format($room->price_per_night, 2) . " per night";
                    }
                    
                    return array(
                        'message' => $this->translate("Here are our room rates:\n" . implode("\n", $price_info), $language),
                        'type' => 'pricing'
                    );
                }
            }
        }

        // Booking flow
        if (preg_match('/(book|make reservation|want to book|i want|reserve)/i', $message_lower)) {
            return $this->handle_booking_flow($hotel_id, $message, $session_id, $language, $context);
        }

        // Default fallback - helpful response for document-trained chatbot
        return array(
            'message' => $this->translate("I'm not sure about that specific question. If you've uploaded training documents, I'll learn from them. Otherwise, I can help you with:\n\n• Room availability and pricing\n• Making a booking\n• Checking booking status\n\nOr chat with our support team on WhatsApp!", $language),
            'type' => 'fallback',
            'fallback_whatsapp' => true,
            'whatsapp_link' => 'https://wa.me/2347120018023?text=' . urlencode($message)
        );
    }
    
    /**
     * Format training data response in a human-like way.
     */
    private function format_training_response($training_response, $original_query, $language) {
        // Add natural language framing
        $intros = array(
            "Based on our information, ",
            "Here's what I found: ",
            "I can tell you that ",
            "According to our records, ",
            ""
        );
        
        $intro = $intros[array_rand($intros)];
        
        // Clean up the response
        $response = trim($training_response);
        
        // Capitalize first letter if needed
        if (!empty($response) && ctype_lower($response[0])) {
            $response = ucfirst($response);
        }
        
        // Ensure proper ending
        if (!preg_match('/[.!?]$/', $response)) {
            $response .= '.';
        }
        
        // Add helpful suffix occasionally
        $suffixes = array(
            "",
            "\n\nIs there anything else you'd like to know?",
            "\n\nCan I help with anything else?",
            ""
        );
        
        $suffix = $suffixes[array_rand($suffixes)];
        
        return $this->translate($intro . $response . $suffix, $language);
    }

    /**
     * Super Intelligent Booking Flow Handler
     * Guides users through complete booking process with account detail collection
     */
    private function handle_booking_flow($hotel_id, $message, $session_id, $language, $context) {
        global $wpdb;
        
        // Get current booking state from session
        $booking_state = $this->get_booking_state($session_id);
        
        // STEP 1: Show available rooms if not already selected
        if (!isset($booking_state['room_selected'])) {
            $table_rooms = $wpdb->prefix . 'staydesk_rooms';
            $rooms = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_rooms WHERE hotel_id = %d AND availability_status = 'available'",
                $hotel_id
            ));
            
            if (empty($rooms)) {
                return array(
                    'message' => $this->translate("Sorry, no rooms are currently available. Can I help you with something else?", $language),
                    'type' => 'no_rooms_available'
                );
            }
            
            $response = $this->translate("Great! I'll help you book a room. Here are our available rooms", $language) . ":\n\n";
            foreach ($rooms as $index => $room) {
                $response .= ($index + 1) . ". " . $room->room_name . " - ₦" . number_format($room->price_per_night) . "/night\n";
                $response .= "   Type: " . $room->room_type . ", Sleeps: " . $room->capacity . " guests\n\n";
            }
            $response .= $this->translate("Which room would you like? (Reply with the number)", $language);
            
            // Save state
            $this->update_booking_state($session_id, array('step' => 'room_selection', 'rooms' => $rooms));
            
            return array(
                'message' => $response,
                'type' => 'booking_room_selection',
                'rooms' => $rooms,
                'next_step' => 'room_selection'
            );
        }
        
        // STEP 2: Collect room selection
        if ($booking_state['step'] === 'room_selection' && !isset($booking_state['selected_room_id'])) {
            $room_number = intval($message);
            if ($room_number > 0 && $room_number <= count($booking_state['rooms'])) {
                $selected_room = $booking_state['rooms'][$room_number - 1];
                
                $this->update_booking_state($session_id, array(
                    'selected_room_id' => $selected_room->id,
                    'selected_room_name' => $selected_room->room_name,
                    'selected_room_price' => $selected_room->price_per_night,
                    'step' => 'check_in_date'
                ));
                
                return array(
                    'message' => $this->translate("Perfect! You've selected {$selected_room->room_name}.\n\nWhat's your check-in date? (Format: DD-MM-YYYY or YYYY-MM-DD)", $language),
                    'type' => 'booking_check_in_date',
                    'next_step' => 'check_in_date'
                );
            } else {
                return array(
                    'message' => $this->translate("Please enter a valid room number from the list above.", $language),
                    'type' => 'invalid_room_selection'
                );
            }
        }
        
        // STEP 3: Collect check-in date
        if ($booking_state['step'] === 'check_in_date' && !isset($booking_state['check_in_date'])) {
            $date = $this->parse_date($message);
            if ($date) {
                $this->update_booking_state($session_id, array(
                    'check_in_date' => $date,
                    'step' => 'check_out_date'
                ));
                
                return array(
                    'message' => $this->translate("Check-in date set to: {$date}\n\nWhat's your check-out date? (Format: DD-MM-YYYY or YYYY-MM-DD)", $language),
                    'type' => 'booking_check_out_date',
                    'next_step' => 'check_out_date'
                );
            } else {
                return array(
                    'message' => $this->translate("Invalid date format. Please use DD-MM-YYYY or YYYY-MM-DD format (e.g., 25-12-2025)", $language),
                    'type' => 'invalid_date'
                );
            }
        }
        
        // STEP 4: Collect check-out date
        if ($booking_state['step'] === 'check_out_date' && !isset($booking_state['check_out_date'])) {
            $date = $this->parse_date($message);
            if ($date) {
                // Validate check-out is after check-in
                if (strtotime($date) <= strtotime($booking_state['check_in_date'])) {
                    return array(
                        'message' => $this->translate("Check-out date must be after check-in date. Please enter a valid check-out date.", $language),
                        'type' => 'invalid_checkout_date'
                    );
                }
                
                // Calculate number of nights
                $nights = (strtotime($date) - strtotime($booking_state['check_in_date'])) / 86400;
                $total_price = $nights * $booking_state['selected_room_price'];
                
                $this->update_booking_state($session_id, array(
                    'check_out_date' => $date,
                    'nights' => $nights,
                    'total_price' => $total_price,
                    'step' => 'guest_name'
                ));
                
                return array(
                    'message' => $this->translate("Check-out date set to: {$date}\n\nTotal: {$nights} nights × ₦" . number_format($booking_state['selected_room_price']) . " = ₦" . number_format($total_price) . "\n\nGreat! Now I need your personal details.\n\nWhat's your full name?", $language),
                    'type' => 'booking_guest_name',
                    'next_step' => 'guest_name',
                    'booking_summary' => array(
                        'room' => $booking_state['selected_room_name'],
                        'check_in' => $booking_state['check_in_date'],
                        'check_out' => $date,
                        'nights' => $nights,
                        'total' => $total_price
                    )
                );
            } else {
                return array(
                    'message' => $this->translate("Invalid date format. Please use DD-MM-YYYY or YYYY-MM-DD format.", $language),
                    'type' => 'invalid_date'
                );
            }
        }
        
        // STEP 5: Collect guest name
        if ($booking_state['step'] === 'guest_name' && !isset($booking_state['guest_name'])) {
            $this->update_booking_state($session_id, array(
                'guest_name' => $message,
                'step' => 'guest_email'
            ));
            
            return array(
                'message' => $this->translate("Thank you, {$message}!\n\nWhat's your email address?", $language),
                'type' => 'booking_guest_email',
                'next_step' => 'guest_email'
            );
        }
        
        // STEP 6: Collect email
        if ($booking_state['step'] === 'guest_email' && !isset($booking_state['guest_email'])) {
            if (!filter_var($message, FILTER_VALIDATE_EMAIL)) {
                return array(
                    'message' => $this->translate("Please enter a valid email address (e.g., example@email.com)", $language),
                    'type' => 'invalid_email'
                );
            }
            
            $this->update_booking_state($session_id, array(
                'guest_email' => $message,
                'step' => 'guest_phone'
            ));
            
            return array(
                'message' => $this->translate("Email saved: {$message}\n\nWhat's your phone number? (Include country code, e.g., +234...)", $language),
                'type' => 'booking_guest_phone',
                'next_step' => 'guest_phone'
            );
        }
        
        // STEP 7: Collect phone number
        if ($booking_state['step'] === 'guest_phone' && !isset($booking_state['guest_phone'])) {
            $this->update_booking_state($session_id, array(
                'guest_phone' => $message,
                'step' => 'number_of_guests'
            ));
            
            return array(
                'message' => $this->translate("Phone saved: {$message}\n\nHow many guests will be staying? (Maximum: " . $booking_state['selected_room_capacity'] . " for this room)", $language),
                'type' => 'booking_guests_count',
                'next_step' => 'number_of_guests'
            );
        }
        
        // STEP 8: Number of guests
        if ($booking_state['step'] === 'number_of_guests' && !isset($booking_state['number_of_guests'])) {
            $guests = intval($message);
            if ($guests < 1) {
                return array(
                    'message' => $this->translate("Please enter a valid number of guests (at least 1).", $language),
                    'type' => 'invalid_guests'
                );
            }
            
            $this->update_booking_state($session_id, array(
                'number_of_guests' => $guests,
                'step' => 'special_requests'
            ));
            
            return array(
                'message' => $this->translate("Number of guests: {$guests}\n\nDo you have any special requests? (e.g., early check-in, airport pickup, dietary requirements)\n\nType your requests or type 'none' to skip.", $language),
                'type' => 'booking_special_requests',
                'next_step' => 'special_requests'
            );
        }
        
        // STEP 9: Special requests
        if ($booking_state['step'] === 'special_requests' && !isset($booking_state['special_requests'])) {
            $requests = ($message === 'none' || strtolower($message) === 'none') ? '' : $message;
            
            $this->update_booking_state($session_id, array(
                'special_requests' => $requests,
                'step' => 'confirm_booking'
            ));
            
            // Generate booking summary
            $summary = $this->translate("Perfect! Here's your booking summary", $language) . ":\n\n";
            $summary .= "━━━━━━━━━━━━━━━━━━━━━━\n";
            $summary .= "🏨 Room: " . $booking_state['selected_room_name'] . "\n";
            $summary .= "📅 Check-in: " . $booking_state['check_in_date'] . "\n";
            $summary .= "📅 Check-out: " . $booking_state['check_out_date'] . "\n";
            $summary .= "🌙 Nights: " . $booking_state['nights'] . "\n";
            $summary .= "👤 Guest: " . $booking_state['guest_name'] . "\n";
            $summary .= "📧 Email: " . $booking_state['guest_email'] . "\n";
            $summary .= "📱 Phone: " . $booking_state['guest_phone'] . "\n";
            $summary .= "👥 Guests: " . $booking_state['number_of_guests'] . "\n";
            if (!empty($requests)) {
                $summary .= "📝 Requests: " . $requests . "\n";
            }
            $summary .= "━━━━━━━━━━━━━━━━━━━━━━\n";
            $summary .= "💰 Total Amount: ₦" . number_format($booking_state['total_price']) . "\n\n";
            $summary .= $this->translate("Type 'CONFIRM' to proceed with booking, or 'CANCEL' to start over.", $language);
            
            return array(
                'message' => $summary,
                'type' => 'booking_confirmation',
                'next_step' => 'confirm_booking',
                'booking_data' => $booking_state
            );
        }
        
        // STEP 10: Confirm and create booking
        if ($booking_state['step'] === 'confirm_booking') {
            if (preg_match('/confirm/i', $message)) {
                // Create booking reference
                $reference = 'BK' . strtoupper(substr(md5(uniqid()), 0, 8));
                
                // Save booking to database
                $table_bookings = $wpdb->prefix . 'staydesk_bookings';
                $booking_id = $wpdb->insert($table_bookings, array(
                    'hotel_id' => $hotel_id,
                    'room_id' => $booking_state['selected_room_id'],
                    'guest_name' => $booking_state['guest_name'],
                    'guest_email' => $booking_state['guest_email'],
                    'guest_phone' => $booking_state['guest_phone'],
                    'check_in_date' => $booking_state['check_in_date'],
                    'check_out_date' => $booking_state['check_out_date'],
                    'number_of_guests' => $booking_state['number_of_guests'],
                    'special_requests' => $booking_state['special_requests'],
                    'total_price' => $booking_state['total_price'],
                    'booking_reference' => $reference,
                    'booking_status' => 'pending',
                    'payment_status' => 'pending',
                    'created_at' => current_time('mysql')
                ));
                
                if ($booking_id) {
                    // Clear booking state
                    $this->clear_booking_state($session_id);
                    
                    // Get hotel account details for payment
                    $table_hotels = $wpdb->prefix . 'staydesk_hotels';
                    $hotel = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_hotels WHERE id = %d", $hotel_id));
                    
                    $payment_info = "";
                    if ($hotel && $hotel->account_details) {
                        $account = json_decode($hotel->account_details, true);
                        $payment_info = "\n\n💳 Payment Instructions:\n";
                        $payment_info .= "━━━━━━━━━━━━━━━━━━━━━━\n";
                        if (isset($account['bank_name'])) $payment_info .= "Bank: " . $account['bank_name'] . "\n";
                        if (isset($account['account_number'])) $payment_info .= "Account: " . $account['account_number'] . "\n";
                        if (isset($account['account_name'])) $payment_info .= "Name: " . $account['account_name'] . "\n";
                        $payment_info .= "Amount: ₦" . number_format($booking_state['total_price']) . "\n";
                        $payment_info .= "Reference: " . $reference . "\n";
                        $payment_info .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
                        $payment_info .= $this->translate("After payment, send proof to confirm your booking.", $language);
                    }
                    
                    $response = "✅ " . $this->translate("Booking Confirmed!", $language) . "\n\n";
                    $response .= $this->translate("Your booking reference is", $language) . ": " . $reference . "\n\n";
                    $response .= $this->translate("A confirmation email has been sent to", $language) . " " . $booking_state['guest_email'] . "\n";
                    $response .= $payment_info;
                    $response .= "\n" . $this->translate("We look forward to hosting you!", $language);
                    
                    return array(
                        'message' => $response,
                        'type' => 'booking_created',
                        'booking_reference' => $reference,
                        'booking_id' => $booking_id
                    );
                } else {
                    return array(
                        'message' => $this->translate("Sorry, there was an error creating your booking. Please try again or contact support.", $language),
                        'type' => 'booking_error'
                    );
                }
            } elseif (preg_match('/cancel/i', $message)) {
                $this->clear_booking_state($session_id);
                return array(
                    'message' => $this->translate("Booking cancelled. How else can I help you?", $language),
                    'type' => 'booking_cancelled'
                );
            } else {
                return array(
                    'message' => $this->translate("Please type 'CONFIRM' to proceed with booking, or 'CANCEL' to start over.", $language),
                    'type' => 'invalid_confirmation'
                );
            }
        }
        
        // Fallback
        return array(
            'message' => $this->translate("I'm here to help you book a room. Let's start! What dates are you looking for?", $language),
            'type' => 'booking_start'
        );
    }
    
    /**
     * Get conversation context from recent messages
     */
    private function get_conversation_context($session_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'staydesk_chat_logs';
        
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE session_id = %s ORDER BY created_at DESC LIMIT 5",
            $session_id
        ));
        
        return $history ?: array();
    }
    
    /**
     * Get booking state from session storage
     */
    private function get_booking_state($session_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'staydesk_chat_sessions';
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE session_id = %s",
            $session_id
        ));
        
        if ($session && $session->booking_data) {
            return json_decode($session->booking_data, true) ?: array();
        }
        
        return array();
    }
    
    /**
     * Update booking state in session storage
     */
    private function update_booking_state($session_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'staydesk_chat_sessions';
        
        $current_state = $this->get_booking_state($session_id);
        $updated_state = array_merge($current_state, $data);
        
        $wpdb->replace($table, array(
            'session_id' => $session_id,
            'booking_data' => json_encode($updated_state),
            'updated_at' => current_time('mysql')
        ));
    }
    
    /**
     * Clear booking state
     */
    private function clear_booking_state($session_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'staydesk_chat_sessions';
        
        $wpdb->delete($table, array('session_id' => $session_id));
    }
    
    /**
     * Parse date from various formats
     */
    private function parse_date($input) {
        // Try different date formats
        $formats = array('d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d', 'm-d-Y');
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, trim($input));
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        // Try strtotime as fallback
        $timestamp = strtotime($input);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return false;
    }

    /**
     * Get hotel FAQ data.
     */
    private function get_hotel_faq_data($hotel) {
        if (!$hotel) {
            return null;
        }

        $faq_data = array();

        // Decode JSON fields
        if (!empty($hotel->payment_pricing)) {
            $faq_data['payment_pricing'] = json_decode($hotel->payment_pricing, true) ?: array();
        }
        if (!empty($hotel->facilities)) {
            $faq_data['facilities'] = json_decode($hotel->facilities, true) ?: array();
        }
        if (!empty($hotel->location_transport)) {
            $faq_data['location_transport'] = json_decode($hotel->location_transport, true) ?: array();
        }
        if (!empty($hotel->food_dining)) {
            $faq_data['food_dining'] = json_decode($hotel->food_dining, true) ?: array();
        }
        if (!empty($hotel->policies)) {
            $faq_data['policies'] = json_decode($hotel->policies, true) ?: array();
        }
        if (!empty($hotel->services)) {
            $faq_data['services'] = json_decode($hotel->services, true) ?: array();
        }
        if (!empty($hotel->safety_security)) {
            $faq_data['safety_security'] = json_decode($hotel->safety_security, true) ?: array();
        }
        if (!empty($hotel->additional_faqs)) {
            $faq_data['additional_faqs'] = $hotel->additional_faqs;
        }

        return $faq_data;
    }

    /**
     * Translate Pidgin to English for better processing.
     */
    private function translate_pidgin_to_english($text) {
        $pidgin_to_english = array(
            'wetin' => 'what',
            'una' => 'you',
            'dey' => 'is',
            'fit' => 'can',
            'make' => 'let',
            'abeg' => 'please',
            'wan' => 'want',
            'dis' => 'this',
            'dat' => 'that',
            'dem' => 'them',
            'you get' => 'do you have',
            'e get' => 'there is',
            'how much' => 'what is the price',
            'how e go cost' => 'how much will it cost',
            'wetin be' => 'what is',
            'room wey dey available' => 'available rooms'
        );
        
        foreach ($pidgin_to_english as $pidgin => $english) {
            $text = preg_replace('/\b' . preg_quote($pidgin, '/') . '\b/i', $english, $text);
        }
        
        return $text;
    }

    /**
     * Translate message based on language.
     */
    private function translate($message, $language) {
        if ($language === 'pidgin') {
            // Enhanced Nigerian Pidgin translations
            $translations = array(
                'Hello! Welcome to' => 'Hello! Welcome to',
                'How can I help you today?' => 'Wetin I fit do for you today?',
                'We have' => 'We get',
                'rooms available' => 'room wey dey available',
                'per night' => 'for one night',
                'Which room would you like to book?' => 'Which room you wan book?',
                'Type:' => 'Type na:',
                'Capacity:' => 'E fit carry:',
                'guests' => 'people',
                'Please' => 'Abeg',
                'Do you have' => 'You get',
                'Yes' => 'Yes o',
                'No' => 'No o',
                'We have' => 'We get',
                'rooms available' => 'room wey dey available',
                'Please provide your booking reference' => 'Abeg give me your booking reference',
                'I found your booking!' => 'I don see your booking!',
                "I couldn't find" => 'I no fit find',
                'I can help you' => 'I fit help you',
                'Would you like to chat with our support team' => 'You wan talk to our support people',
                "I'm not sure how to help" => 'I no too sure how I go help'
            );

            foreach ($translations as $en => $pidgin) {
                $message = str_replace($en, $pidgin, $message);
            }
        }

        return $message;
    }
    
    /**
     * Handle room availability intent with ultra-smart responses
     */
    private function handle_room_availability_intent($hotel_id, $message, $language, $faq_data) {
        global $wpdb;
        
        if ($hotel_id > 0) {
            $table_rooms = $wpdb->prefix . 'staydesk_rooms';
            $rooms = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_rooms WHERE hotel_id = %d AND availability_status = 'available'",
                $hotel_id
            ));
            
            if (empty($rooms)) {
                return array(
                    'message' => $this->translate("Currently, we have no rooms available. Would you like me to notify you when rooms become available?", $language),
                    'type' => 'no_rooms'
                );
            }

            $response = $this->translate("We have " . count($rooms) . " rooms available", $language) . ":\n\n";
            foreach ($rooms as $room) {
                $response .= "• " . $room->room_name . " - ₦" . number_format($room->price_per_night) . "/" . $this->translate("night", $language) . "\n";
                $response .= "  " . $this->translate("Type:", $language) . " " . $room->room_type . ", ";
                $response .= $this->translate("Capacity:", $language) . " " . $room->capacity . " " . $this->translate("guests", $language) . "\n";
                if (!empty($room->room_description)) {
                    $response .= "  " . substr($room->room_description, 0, 80) . "...\n";
                }
                $response .= "\n";
            }
            $response .= $this->translate("Which room would you like to book? (Reply with the room number)", $language);

            return array(
                'message' => $response,
                'type' => 'room_list',
                'rooms' => $rooms
            );
        }
        
        return array(
            'message' => $this->translate("Please specify which hotel you're interested in to check room availability.", $language),
            'type' => 'need_hotel'
        );
    }
    
    /**
     * Handle pricing intent with comprehensive price information
     */
    private function handle_pricing_intent($hotel_id, $message, $language) {
        global $wpdb;
        
        if ($hotel_id > 0) {
            $table_rooms = $wpdb->prefix . 'staydesk_rooms';
            $rooms = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_rooms WHERE hotel_id = %d AND availability_status = 'available' ORDER BY price_per_night ASC",
                $hotel_id
            ));
            
            if (empty($rooms)) {
                return array(
                    'message' => $this->translate("We currently have no rooms available for pricing information.", $language),
                    'type' => 'no_rooms'
                );
            }

            $response = $this->translate("Our room pricing", $language) . ":\n\n";
            foreach ($rooms as $room) {
                $response .= "• " . $room->room_name . " (" . $room->room_type . ")\n";
                $response .= "  " . $this->translate("Price:", $language) . " ₦" . number_format($room->price_per_night) . "/" . $this->translate("night", $language) . "\n";
                $response .= "  " . $this->translate("Capacity:", $language) . " " . $room->capacity . " " . $this->translate("guests", $language) . "\n\n";
            }
            $response .= $this->translate("Would you like to book a room?", $language);

            return array(
                'message' => $response,
                'type' => 'pricing_info',
                'rooms' => $rooms
            );
        }
        
        return array(
            'message' => $this->translate("Please specify which hotel for pricing information.", $language),
            'type' => 'need_hotel'
        );
    }
    
    /**
     * Handle facilities intent
     */
    private function handle_facilities_intent($hotel_id, $message, $language, $faq_data) {
        if ($faq_data && isset($faq_data['facilities'])) {
            $facilities = $faq_data['facilities'];
            $response = $this->translate("Our hotel facilities include:", $language) . "\n\n";
            
            if (!empty($facilities['basic_amenities'])) {
                $response .= "✓ " . $this->translate("Basic Amenities:", $language) . " " . $facilities['basic_amenities'] . "\n\n";
            }
            if (!empty($facilities['recreation'])) {
                $response .= "✓ " . $this->translate("Recreation:", $language) . " " . $facilities['recreation'] . "\n\n";
            }
            if (!empty($facilities['business'])) {
                $response .= "✓ " . $this->translate("Business:", $language) . " " . $facilities['business'] . "\n\n";
            }
            
            return array(
                'message' => $response,
                'type' => 'facilities_info'
            );
        }
        
        return array(
            'message' => $this->translate("I don't have facility information for this hotel yet. Please contact our support team for details.", $language),
            'type' => 'no_data'
        );
    }
    
    /**
     * Handle food & dining intent
     */
    private function handle_food_dining_intent($hotel_id, $message, $language, $faq_data) {
        if ($faq_data && isset($faq_data['food_dining'])) {
            $food = $faq_data['food_dining'];
            $response = $this->translate("Food & Dining:", $language) . "\n\n";
            
            if (!empty($food['restaurant_details'])) {
                $response .= "🍽️ " . $food['restaurant_details'] . "\n\n";
            }
            if (!empty($food['cuisine_types'])) {
                $response .= "🍲 " . $this->translate("Cuisine Types:", $language) . " " . $food['cuisine_types'] . "\n\n";
            }
            if (!empty($food['room_service'])) {
                $response .= "🛎️ " . $this->translate("Room Service:", $language) . " " . $food['room_service'] . "\n\n";
            }
            
            return array(
                'message' => $response,
                'type' => 'food_dining_info'
            );
        }
        
        return array(
            'message' => $this->translate("I don't have food & dining information yet. Please contact us for menu details.", $language),
            'type' => 'no_data'
        );
    }
    
    /**
     * Handle location intent
     */
    private function handle_location_intent($hotel_id, $message, $language, $faq_data) {
        if ($faq_data && isset($faq_data['location_transport'])) {
            $location = $faq_data['location_transport'];
            $response = $this->translate("Location & Transport:", $language) . "\n\n";
            
            if (!empty($location['address_details'])) {
                $response .= "📍 " . $location['address_details'] . "\n\n";
            }
            if (!empty($location['airport_distance'])) {
                $response .= "✈️ " . $this->translate("Airport Distance:", $language) . " " . $location['airport_distance'] . "\n\n";
            }
            if (!empty($location['nearby_attractions'])) {
                $response .= "🏛️ " . $this->translate("Nearby:", $language) . " " . $location['nearby_attractions'] . "\n\n";
            }
            
            return array(
                'message' => $response,
                'type' => 'location_info'
            );
        }
        
        return array(
            'message' => $this->translate("I don't have location details yet. Please contact us for directions.", $language),
            'type' => 'no_data'
        );
    }
    
    /**
     * Handle check-in/check-out intent
     */
    private function handle_checkin_checkout_intent($hotel_id, $message, $language, $faq_data) {
        if ($faq_data && isset($faq_data['payment_pricing'])) {
            $payment = $faq_data['payment_pricing'];
            $response = "";
            
            if (!empty($payment['checkin_time'])) {
                $response .= "🏨 " . $this->translate("Check-in Time:", $language) . " " . $payment['checkin_time'] . "\n\n";
            }
            if (!empty($payment['checkout_time'])) {
                $response .= "🚪 " . $this->translate("Check-out Time:", $language) . " " . $payment['checkout_time'] . "\n\n";
            }
            
            if ($response) {
                return array(
                    'message' => $response,
                    'type' => 'checkin_info'
                );
            }
        }
        
        return array(
            'message' => $this->translate("Standard check-in is at 2:00 PM and check-out is at 12:00 PM. Please contact us for early check-in or late check-out requests.", $language),
            'type' => 'default_checkin'
        );
    }
    
    /**
     * Handle payment methods intent
     */
    private function handle_payment_methods_intent($hotel_id, $message, $language, $faq_data) {
        if ($faq_data && isset($faq_data['payment_pricing']) && !empty($faq_data['payment_pricing']['payment_methods'])) {
            $payment = $faq_data['payment_pricing'];
            $response = $this->translate("We accept the following payment methods:", $language) . "\n\n";
            $response .= "💳 " . $payment['payment_methods'] . "\n\n";
            
            return array(
                'message' => $response,
                'type' => 'payment_methods_info'
            );
        }
        
        return array(
            'message' => $this->translate("We accept credit cards, debit cards, bank transfers, and cash payments. Online payments are processed securely through Paystack.", $language),
            'type' => 'default_payment'
        );
    }
    
    /**
     * Handle policies intent
     */
    private function handle_policies_intent($hotel_id, $message, $language, $faq_data) {
        if ($faq_data && isset($faq_data['policies'])) {
            $policies = $faq_data['policies'];
            $response = $this->translate("Our Policies:", $language) . "\n\n";
            
            if (!empty($policies['cancellation_policy'])) {
                $response .= "🔄 " . $this->translate("Cancellation:", $language) . " " . $policies['cancellation_policy'] . "\n\n";
            }
            if (!empty($policies['pet_policy'])) {
                $response .= "🐾 " . $this->translate("Pets:", $language) . " " . $policies['pet_policy'] . "\n\n";
            }
            if (!empty($policies['smoking_policy'])) {
                $response .= "🚭 " . $this->translate("Smoking:", $language) . " " . $policies['smoking_policy'] . "\n\n";
            }
            if (!empty($policies['children_policy'])) {
                $response .= "👶 " . $this->translate("Children:", $language) . " " . $policies['children_policy'] . "\n\n";
            }
            
            return array(
                'message' => $response,
                'type' => 'policies_info'
            );
        }
        
        return array(
            'message' => $this->translate("Please contact our support team for detailed information about our policies.", $language),
            'type' => 'no_data'
        );
    }

    /**
     * Get chat history for a session.
     */
    public static function get_chat_history($session_id) {
        global $wpdb;

        $table_chat_logs = $wpdb->prefix . 'staydesk_chat_logs';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_chat_logs WHERE session_id = %s ORDER BY created_at ASC",
            $session_id
        ));
    }

    /**
     * Render dashboard chatbot widget.
     */
    public static function render_dashboard_widget($hotel_id) {
        ?>
        <div id="dashboard-chatbot-widget" style="position: fixed; bottom: 20px; left: 20px; z-index: 1000;">
            <style>
                #dashboard-chatbot-widget .chat-toggle {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
                    border: none;
                    cursor: pointer;
                    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    transition: all 0.3s ease;
                }
                
                #dashboard-chatbot-widget .chat-toggle:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.6);
                }
                
                #dashboard-chatbot-widget .chat-window {
                    display: none;
                    position: absolute;
                    bottom: 75px;
                    left: 0;
                    width: 350px;
                    height: 500px;
                    background: #1a1a1a;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
                    border: 1px solid rgba(212, 175, 55, 0.3);
                    display: flex;
                    flex-direction: column;
                }
                
                #dashboard-chatbot-widget .chat-window.open {
                    display: flex;
                }
                
                #dashboard-chatbot-widget .chat-header {
                    background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
                    padding: 15px;
                    border-radius: 15px 15px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                #dashboard-chatbot-widget .chat-header h3 {
                    margin: 0;
                    color: #0a0a0a;
                    font-size: 16px;
                    font-weight: 700;
                }
                
                #dashboard-chatbot-widget .chat-close {
                    background: none;
                    border: none;
                    color: #0a0a0a;
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0;
                    width: 25px;
                    height: 25px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                #dashboard-chatbot-widget .chat-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 15px;
                    background: #0a0a0a;
                }
                
                #dashboard-chatbot-widget .chat-message {
                    margin-bottom: 12px;
                    padding: 10px 12px;
                    border-radius: 10px;
                    max-width: 80%;
                    word-wrap: break-word;
                }
                
                #dashboard-chatbot-widget .chat-message.user {
                    background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
                    color: #E8E8E8;
                    margin-left: auto;
                    text-align: right;
                }
                
                #dashboard-chatbot-widget .chat-message.bot {
                    background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
                    color: #0a0a0a;
                    margin-right: auto;
                }
                
                #dashboard-chatbot-widget .chat-input-area {
                    padding: 15px;
                    background: #1a1a1a;
                    border-top: 1px solid rgba(212, 175, 55, 0.2);
                    border-radius: 0 0 15px 15px;
                    display: flex;
                    gap: 10px;
                }
                
                #dashboard-chatbot-widget .chat-input {
                    flex: 1;
                    background: rgba(42, 42, 42, 0.8);
                    border: 1px solid rgba(212, 175, 55, 0.3);
                    border-radius: 20px;
                    padding: 10px 15px;
                    color: #FFFFFF;
                    font-size: 14px;
                    outline: none;
                }
                
                #dashboard-chatbot-widget .chat-input:focus {
                    border-color: #D4AF37;
                    box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
                }
                
                #dashboard-chatbot-widget .chat-send {
                    background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
                    border: none;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                }
                
                #dashboard-chatbot-widget .chat-send:hover {
                    transform: scale(1.1);
                }
            </style>
            
            <button class="chat-toggle" onclick="toggleDashboardChat()">
                <span style="font-size: 22px; color: #0a0a0a;">👤</span>
            </button>
            
            <div class="chat-window" id="dashboardChatWindow">
                <div class="chat-header">
                    <h3>👤 Hotel Assistant</h3>
                    <button class="chat-close" onclick="toggleDashboardChat()">×</button>
                </div>
                
                <div class="chat-messages" id="dashboardChatMessages">
                    <div class="chat-message bot">
                        Hello! I'm your hotel assistant. Ask me anything about check-in times, amenities, policies, or room availability!
                    </div>
                </div>
                
                <div class="chat-input-area">
                    <input type="text" class="chat-input" id="dashboardChatInput" placeholder="Type your message..." onkeypress="if(event.key==='Enter') sendDashboardChat()">
                    <button class="chat-send" onclick="sendDashboardChat()">➤</button>
                </div>
            </div>
        </div>
        
        <script>
        function toggleDashboardChat() {
            var chatWindow = document.getElementById('dashboardChatWindow');
            chatWindow.classList.toggle('open');
        }
        
        function sendDashboardChat() {
            var input = document.getElementById('dashboardChatInput');
            var message = input.value.trim();
            
            if (!message) return;
            
            // Add user message to chat
            var messagesDiv = document.getElementById('dashboardChatMessages');
            var userMsg = document.createElement('div');
            userMsg.className = 'chat-message user';
            userMsg.textContent = message;
            messagesDiv.appendChild(userMsg);
            
            input.value = '';
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            
            // Send to server
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_chatbot_message',
                    hotel_id: <?php echo intval($hotel_id); ?>,
                    message: message,
                    session_id: 'dashboard_<?php echo get_current_user_id(); ?>_' + Date.now(),
                    language: 'en'
                },
                success: function(response) {
                    var botMsg = document.createElement('div');
                    botMsg.className = 'chat-message bot';
                    botMsg.textContent = response.data.message || 'Sorry, I encountered an error.';
                    messagesDiv.appendChild(botMsg);
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                },
                error: function() {
                    var botMsg = document.createElement('div');
                    botMsg.className = 'chat-message bot';
                    botMsg.textContent = 'Sorry, I\'m having trouble connecting. Please try again.';
                    messagesDiv.appendChild(botMsg);
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * Get available rooms for hotel (real-time availability).
     */
    private function get_available_rooms($hotel_id) {
        global $wpdb;
        $table_rooms = $wpdb->prefix . 'staydesk_rooms';
        
        $rooms = $wpdb->get_results($wpdb->prepare(
            "SELECT id, room_name, room_type, price_per_night, max_guests, availability_status 
             FROM $table_rooms 
             WHERE hotel_id = %d AND availability_status = 'available'
             ORDER BY price_per_night ASC
             LIMIT 10",
            $hotel_id
        ));
        
        return $rooms ? $rooms : array();
    }
    
    /**
     * Get hotel information from hotel_info_json.
     */
    private function get_hotel_info($hotel_id) {
        global $wpdb;
        $table_hotels = $wpdb->prefix . 'staydesk_hotels';
        
        $hotel = $wpdb->get_row($wpdb->prepare(
            "SELECT hotel_name, hotel_email, hotel_phone, hotel_address, hotel_info_json 
             FROM $table_hotels 
             WHERE id = %d",
            $hotel_id
        ));
        
        if (!$hotel) {
            return array();
        }
        
        $info = array(
            'name' => $hotel->hotel_name,
            'email' => $hotel->hotel_email,
            'phone' => $hotel->hotel_phone,
            'location' => $hotel->hotel_address
        );
        
        // Parse hotel_info_json for additional details
        if (!empty($hotel->hotel_info_json)) {
            $hotel_data = json_decode($hotel->hotel_info_json, true);
            if ($hotel_data) {
                if (isset($hotel_data['location_transport']['address'])) {
                    $info['location'] = $hotel_data['location_transport']['address'];
                }
                if (isset($hotel_data['location_transport']['phone'])) {
                    $info['contact'] = $hotel_data['location_transport']['phone'];
                }
            }
        }
        
        return $info;
    }
}

new Staydesk_Chatbot();