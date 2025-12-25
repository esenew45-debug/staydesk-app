<?php
if (!is_user_logged_in()) {
    wp_redirect(home_url('/staydesk-login'));
    exit;
}

wp_enqueue_script('jquery');

global $wpdb;
$user_id = get_current_user_id();
$table_hotels = $wpdb->prefix . 'staydesk_hotels';
$hotel = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_hotels WHERE user_id = %d",
    $user_id
));

if (!$hotel) {
    echo '<p>Hotel profile not found.</p>';
    return;
}

$table_bookings = $wpdb->prefix . 'staydesk_bookings';
$table_rooms = $wpdb->prefix . 'staydesk_rooms';
$table_guests = $wpdb->prefix . 'staydesk_guests';

// Disable caching to ensure fresh data on refresh
$wpdb->flush();
wp_cache_flush();

$bookings = $wpdb->get_results($wpdb->prepare("
    SELECT b.*, r.room_name, r.room_type, g.guest_name, g.guest_email, g.guest_phone
    FROM $table_bookings b
    LEFT JOIN $table_rooms r ON b.room_id = r.id
    LEFT JOIN $table_guests g ON b.guest_id = g.id
    WHERE b.hotel_id = %d
    ORDER BY b.created_at DESC
", $hotel->id), ARRAY_A);

// Convert to objects for compatibility
$bookings = array_map(function($booking) {
    return (object) $booking;
}, $bookings);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=0.5">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --sapphire: #1e3a5f;
            --sapphire-light: #2d5a8a;
            --sapphire-glow: rgba(30, 58, 95, 0.4);
            --accent: #6bb3ff;
            --accent-light: #8ec5ff;
            --bg-dark: #0f1419;
            --bg-darker: #080b0e;
            --glass-bg: rgba(30, 58, 95, 0.1);
            --glass-border: rgba(107, 179, 255, 0.15);
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --font-display: 'Nunito', -apple-system, sans-serif;
            --font-body: 'Nunito', -apple-system, sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-darker);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--sapphire), var(--sapphire-light));
            border-radius: 3px;
        }
        
        /* Remove all underlines from links */
        a {
            text-decoration: none !important;
        }
        
        body {
            font-family: var(--font-body);
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 25px;
        }
        
        .bookings-container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .page-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            border: 1px solid var(--glass-border);
        }
        
        .page-header h1 {
            font-family: var(--font-display);
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
            font-size: 1.4rem;
        }
        
        .filter-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-section select,
        .filter-section input {
            padding: 10px 14px;
            background: rgba(30, 58, 95, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.8rem;
            font-family: var(--font-body);
            transition: all 0.3s ease;
        }
        
        .filter-section select:focus,
        .filter-section input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(107, 179, 255, 0.2);
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.75rem;
            font-family: var(--font-body);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn svg {
            width: 14px;
            height: 14px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-back {
            background: rgba(30, 58, 95, 0.3);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
        }
        
        .btn-back:hover {
            background: rgba(30, 58, 95, 0.5);
            border-color: var(--accent);
        }
        
        .bookings-table {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: rgba(30, 58, 95, 0.4);
        }
        
        th {
            padding: 14px 16px;
            text-align: left;
            color: var(--accent);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        td {
            padding: 14px 16px;
            color: var(--text-primary);
            font-size: 0.8rem;
            border-bottom: 1px solid rgba(107, 179, 255, 0.08);
        }
        
        tr:hover {
            background: rgba(30, 58, 95, 0.2);
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .status-confirmed {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .status-cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .status-completed {
            background: rgba(107, 179, 255, 0.15);
            color: var(--accent);
            border: 1px solid rgba(107, 179, 255, 0.3);
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.7rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px var(--sapphire-glow);
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 20px var(--sapphire-glow);
        }
        
        .btn-primary:hover svg {
            filter: drop-shadow(0 0 4px var(--accent));
        }
        
        .btn-secondary {
            background: rgba(30, 58, 95, 0.3);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary:hover {
            background: rgba(30, 58, 95, 0.5);
        }
        
        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state h3 {
            font-family: var(--font-display);
            font-size: 1.3rem;
            margin-bottom: 12px;
            color: var(--text-primary);
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 18px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(30, 58, 95, 0.3);
            border-color: rgba(107, 179, 255, 0.25);
        }
        
        .stat-card h3 {
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(8, 11, 14, 0.9);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: linear-gradient(135deg, #0f1419 0%, #1a2332 100%);
            padding: 28px;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            border: 1px solid var(--glass-border);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-content h2 {
            font-family: var(--font-display);
            color: var(--accent);
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .modal-content label {
            color: var(--text-primary);
            display: block;
            margin-bottom: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 10px 14px;
            background: rgba(30, 58, 95, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: var(--font-body);
            transition: all 0.3s ease;
        }
        
        .modal-content input:focus,
        .modal-content select:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(107, 179, 255, 0.2);
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
        
        /* Toast notifications for real-time updates */
        .booking-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            z-index: 10001;
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .booking-toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .booking-toast.success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .booking-toast.error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php wp_nonce_field('staydesk_nonce', 'staydesk_booking_nonce'); ?>
    <div class="bookings-container">
        <div class="page-header">
            <h1>Bookings Management</h1>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <svg data-feather="plus"></svg>
                    Add Booking
                </button>
                <button class="btn btn-secondary" onclick="location.reload()">
                    <svg data-feather="refresh-cw"></svg>
                    Refresh
                </button>
                <button class="btn btn-back" onclick="window.location.href='<?php echo esc_url(home_url('/staydesk-dashboard')); ?>'">
                    <svg data-feather="arrow-left"></svg>
                    Dashboard
                </button>
            </div>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="value"><?php echo count($bookings); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <div class="value"><?php echo count(array_filter($bookings, fn($b) => $b->booking_status === 'pending')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Confirmed</h3>
                <div class="value"><?php echo count(array_filter($bookings, fn($b) => $b->booking_status === 'confirmed')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <div class="value"><?php echo count(array_filter($bookings, fn($b) => $b->booking_status === 'completed')); ?></div>
            </div>
        </div>
        
        <div class="filter-section">
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
            </select>
            <input type="text" id="searchInput" placeholder="Search by guest name or reference...">
        </div>
        
        <div class="bookings-table">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <h3>No Bookings Yet</h3>
                    <p>Bookings will appear here once guests make reservations</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="booking-row" data-status="<?php echo esc_attr($booking->booking_status); ?>">
                                <td><strong><?php echo esc_html($booking->booking_reference); ?></strong></td>
                                <td>
                                    <?php echo esc_html($booking->guest_name); ?><br>
                                    <small style="color: var(--text-secondary);"><?php echo esc_html($booking->guest_email); ?></small>
                                </td>
                                <td>
                                    <?php echo esc_html($booking->room_name); ?><br>
                                    <small style="color: var(--text-secondary);"><?php echo esc_html($booking->room_type); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking->check_in_date)); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking->check_out_date)); ?></td>
                                <td><strong>₦<?php echo number_format($booking->total_amount, 2); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($booking->booking_status); ?>">
                                        <?php echo ucfirst(esc_html($booking->booking_status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($booking->payment_status); ?>">
                                        <?php echo ucfirst(esc_html($booking->payment_status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking->booking_status === 'pending'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo (int)$booking->id; ?>, 'confirmed')">Confirm</button>
                                    <?php elseif ($booking->booking_status === 'confirmed'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo (int)$booking->id; ?>, 'completed')">Complete</button>
                                    <?php endif; ?>
                                    <button class="btn btn-secondary btn-sm" onclick="editBooking(<?php echo (int)$booking->id; ?>)">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteBooking(<?php echo (int)$booking->id; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Booking Modal -->
    <div id="addBookingModal" class="modal-overlay">
        <div class="modal-content">
            <h2>Add New Booking</h2>
            <form id="addBookingForm">
                <div class="form-group">
                    <label>Room:</label>
                    <select id="add_room_id" required>
                        <option value="">Select a room</option>
                        <?php
                        $table_rooms = $wpdb->prefix . 'staydesk_rooms';
                        $rooms = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_rooms WHERE hotel_id = %d AND availability_status = 'available'", $hotel->id));
                        foreach ($rooms as $room):
                        ?>
                            <option value="<?php echo (int)$room->id; ?>"><?php echo esc_html($room->room_name); ?> - ₦<?php echo number_format($room->price_per_night, 2); ?>/night</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Guest Name:</label>
                    <input type="text" id="add_guest_name" required>
                </div>
                <div class="form-group">
                    <label>Guest Email:</label>
                    <input type="email" id="add_guest_email" required>
                </div>
                <div class="form-group">
                    <label>Guest Phone:</label>
                    <input type="tel" id="add_guest_phone" required>
                </div>
                <div class="form-group">
                    <label>Check-in Date:</label>
                    <input type="date" id="add_check_in" required>
                </div>
                <div class="form-group">
                    <label>Check-out Date:</label>
                    <input type="date" id="add_check_out" required>
                </div>
                <div class="form-group">
                    <label>Number of Guests:</label>
                    <input type="number" id="add_num_guests" required min="1">
                </div>
                <div class="form-group">
                    <label>Special Requests:</label>
                    <textarea id="add_special_requests" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Create Booking</button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Booking Modal -->
    <div id="editBookingModal" class="modal-overlay">
        <div class="modal-content">
            <h2>Edit Booking</h2>
            <form id="editBookingForm">
                <input type="hidden" id="edit_booking_id">
                <div class="form-group">
                    <label>Check-in Date:</label>
                    <input type="date" id="edit_check_in" required>
                </div>
                <div class="form-group">
                    <label>Check-out Date:</label>
                    <input type="date" id="edit_check_out" required>
                </div>
                <div class="form-group">
                    <label>Number of Guests:</label>
                    <input type="number" id="edit_num_guests" required min="1">
                </div>
                <div class="form-group">
                    <label>Special Requests:</label>
                    <textarea id="edit_special_requests" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        jQuery(document).ready(function($) {
            // Filter by status
            $('#statusFilter').on('change', function() {
                const status = $(this).val();
                if (status) {
                    $('.booking-row').hide();
                    $(`.booking-row[data-status="${status}"]`).show();
                } else {
                    $('.booking-row').show();
                }
            });
            
            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.booking-row').each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(searchTerm) > -1);
                });
            });
        });
        
        function updateStatus(bookingId, newStatus) {
            const statusText = newStatus === 'confirmed' ? 'confirm' : 'mark as completed';
            if (!confirm('Are you sure you want to ' + statusText + ' this booking?')) return;
            
            // Get nonce from hidden field
            var nonceValue = jQuery('#staydesk_booking_nonce').val();
            var $row = jQuery('tr.booking-row').filter(function() {
                return jQuery(this).find('button[onclick*="' + bookingId + '"]').length > 0;
            });
            
            // Show loading state
            var $actionButtons = $row.find('td:last-child');
            var originalContent = $actionButtons.html();
            $actionButtons.html('<span style="color: var(--accent);">Updating...</span>');
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_update_booking_status',
                    nonce: nonceValue,
                    booking_id: bookingId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Update the UI in real-time
                        var $statusBadge = $row.find('.status-badge').first();
                        $statusBadge.removeClass('status-pending status-confirmed status-completed status-cancelled')
                                    .addClass('status-' + newStatus)
                                    .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        
                        $row.attr('data-status', newStatus);
                        
                        // Update action buttons based on new status
                        var newButtons = '';
                        if (newStatus === 'confirmed') {
                            newButtons = '<button class="btn btn-primary btn-sm" onclick="updateStatus(' + bookingId + ', \'completed\')">Complete</button> ';
                        }
                        newButtons += '<button class="btn btn-secondary btn-sm" onclick="editBooking(' + bookingId + ')">Edit</button> ';
                        newButtons += '<button class="btn btn-danger btn-sm" onclick="deleteBooking(' + bookingId + ')">Delete</button>';
                        $actionButtons.html(newButtons);
                        
                        // Update stats in real-time
                        updateBookingStats();
                        
                        // Show success toast
                        showToast('✅ Booking status updated to ' + newStatus + '!', 'success');
                        
                        // Re-initialize feather icons
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    } else {
                        $actionButtons.html(originalContent);
                        showToast('❌ Error: ' + (response.data ? response.data.message : 'Unknown error'), 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $actionButtons.html(originalContent);
                    showToast('❌ An error occurred: ' + error, 'error');
                }
            });
        }
        
        // Real-time stats update function
        function updateBookingStats() {
            var pending = jQuery('.booking-row[data-status="pending"]').length;
            var confirmed = jQuery('.booking-row[data-status="confirmed"]').length;
            var completed = jQuery('.booking-row[data-status="completed"]').length;
            var total = jQuery('.booking-row').length;
            
            jQuery('.stat-card').eq(0).find('.value').text(total);
            jQuery('.stat-card').eq(1).find('.value').text(pending);
            jQuery('.stat-card').eq(2).find('.value').text(confirmed);
            jQuery('.stat-card').eq(3).find('.value').text(completed);
        }
        
        // Toast notification function
        function showToast(message, type) {
            var toast = jQuery('<div class="booking-toast ' + type + '">' + message + '</div>');
            jQuery('body').append(toast);
            setTimeout(function() { toast.addClass('show'); }, 10);
            setTimeout(function() { 
                toast.removeClass('show');
                setTimeout(function() { toast.remove(); }, 300);
            }, 3000);
        }
        
        function editBooking(bookingId) {
            // Get booking details
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_get_booking',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        const booking = response.data;
                        document.getElementById('edit_booking_id').value = booking.id;
                        document.getElementById('edit_check_in').value = booking.check_in_date;
                        document.getElementById('edit_check_out').value = booking.check_out_date;
                        document.getElementById('edit_num_guests').value = booking.num_guests;
                        document.getElementById('edit_special_requests').value = booking.special_requests || '';
                        document.getElementById('editBookingModal').style.display = 'flex';
                    } else {
                        alert('Error loading booking details');
                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        }
        
        function closeEditModal() {
            document.getElementById('editBookingModal').style.display = 'none';
        }
        
        function openAddModal() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('add_check_in').setAttribute('min', today);
            document.getElementById('add_check_out').setAttribute('min', today);
            document.getElementById('addBookingModal').style.display = 'flex';
        }
        
        function closeAddModal() {
            document.getElementById('addBookingModal').style.display = 'none';
            document.getElementById('addBookingForm').reset();
        }
        
        // Handle add booking form submission
        jQuery('#addBookingForm').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = jQuery(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span>Creating...</span>');
            
            const formData = {
                action: 'staydesk_create_booking',
                nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                room_id: jQuery('#add_room_id').val(),
                guest_name: jQuery('#add_guest_name').val(),
                guest_email: jQuery('#add_guest_email').val(),
                guest_phone: jQuery('#add_guest_phone').val(),
                check_in_date: jQuery('#add_check_in').val(),
                check_out_date: jQuery('#add_check_out').val(),
                num_guests: jQuery('#add_num_guests').val(),
                special_requests: jQuery('#add_special_requests').val()
            };
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        closeAddModal();
                        showToast('✅ Booking created successfully!', 'success');
                        
                        // Add new booking row to table in real-time
                        if (response.data && response.data.booking) {
                            var booking = response.data.booking;
                            var newRow = createBookingRow(booking);
                            
                            // Check if empty state exists and remove it
                            if (jQuery('.empty-state').length > 0) {
                                jQuery('.bookings-table').html('<table><thead><tr><th>Reference</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Amount</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead><tbody></tbody></table>');
                            }
                            
                            jQuery('.bookings-table tbody').prepend(newRow);
                            updateBookingStats();
                            
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        } else {
                            // Fallback: reload if no booking data returned
                            location.reload();
                        }
                    } else {
                        submitBtn.prop('disabled', false).html(originalText);
                        showToast('❌ Error: ' + (response.data.message || 'Unable to create booking'), 'error');
                    }
                },
                error: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                    showToast('❌ An error occurred while creating the booking.', 'error');
                }
            });
        });
        
        // Helper function to create a booking row HTML
        function createBookingRow(booking) {
            var checkInDate = new Date(booking.check_in_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var checkOutDate = new Date(booking.check_out_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var amount = parseFloat(booking.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
            
            return '<tr class="booking-row" data-status="' + booking.booking_status + '" style="animation: fadeIn 0.3s ease">' +
                '<td><strong>' + (booking.booking_reference || 'N/A') + '</strong></td>' +
                '<td>' + booking.guest_name + '<br><small style="color: var(--text-secondary);">' + booking.guest_email + '</small></td>' +
                '<td>' + (booking.room_name || 'N/A') + '<br><small style="color: var(--text-secondary);">' + (booking.room_type || '') + '</small></td>' +
                '<td>' + checkInDate + '</td>' +
                '<td>' + checkOutDate + '</td>' +
                '<td><strong>₦' + amount + '</strong></td>' +
                '<td><span class="status-badge status-' + booking.booking_status + '">' + booking.booking_status.charAt(0).toUpperCase() + booking.booking_status.slice(1) + '</span></td>' +
                '<td><span class="status-badge status-' + booking.payment_status + '">' + booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1) + '</span></td>' +
                '<td>' +
                    '<button class="btn btn-primary btn-sm" onclick="updateStatus(' + booking.id + ', \'confirmed\')">Confirm</button> ' +
                    '<button class="btn btn-secondary btn-sm" onclick="editBooking(' + booking.id + ')">Edit</button> ' +
                    '<button class="btn btn-danger btn-sm" onclick="deleteBooking(' + booking.id + ')">Delete</button>' +
                '</td>' +
            '</tr>';
        }
        
        function deleteBooking(bookingId) {
            if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) return;
            
            var $row = jQuery('tr.booking-row').filter(function() {
                return jQuery(this).find('button[onclick*="deleteBooking(' + bookingId + ')"]').length > 0;
            });
            
            // Add deletion animation
            $row.css({ opacity: 0.5, transition: 'opacity 0.3s' });
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_delete_booking',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove row with animation
                        $row.css({ 
                            transform: 'translateX(-100%)', 
                            transition: 'all 0.3s ease',
                            opacity: 0
                        });
                        setTimeout(function() {
                            $row.remove();
                            updateBookingStats();
                            
                            // Check if table is now empty
                            if (jQuery('.booking-row').length === 0) {
                                jQuery('.bookings-table table').replaceWith(
                                    '<div class="empty-state"><h3>No Bookings Yet</h3><p>Bookings will appear here once guests make reservations</p></div>'
                                );
                            }
                        }, 300);
                        
                        showToast('✅ Booking deleted successfully!', 'success');
                    } else {
                        $row.css({ opacity: 1 });
                        showToast('❌ Error deleting booking', 'error');
                    }
                },
                error: function() {
                    $row.css({ opacity: 1 });
                    showToast('❌ An error occurred', 'error');
                }
            });
        }
        
        // Handle edit form submission
        jQuery('#editBookingForm').on('submit', function(e) {
            e.preventDefault();
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_edit_booking',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    booking_id: jQuery('#edit_booking_id').val(),
                    check_in_date: jQuery('#edit_check_in').val(),
                    check_out_date: jQuery('#edit_check_out').val(),
                    num_guests: jQuery('#edit_num_guests').val(),
                    special_requests: jQuery('#edit_special_requests').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Booking updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating booking');
                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });
    </script>
</body>
</html>