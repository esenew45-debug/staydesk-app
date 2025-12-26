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

$table_rooms = $wpdb->prefix . 'staydesk_rooms';
$rooms = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_rooms WHERE hotel_id = %d ORDER BY created_at DESC",
    $hotel->id
));

$table_room_types = $wpdb->prefix . 'staydesk_room_types';
$room_types = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_room_types WHERE hotel_id = %d OR hotel_id IS NULL ORDER BY type_name ASC",
    $hotel->id
));
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
        
        .rooms-container {
            max-width: 1400px;
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
        
        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.25);
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
        
        .form-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 28px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid var(--glass-border);
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        .form-section h2 {
            font-family: var(--font-display);
            color: var(--accent);
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            color: var(--text-primary);
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
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
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(107, 179, 255, 0.2);
        }
        
        .rooms-table {
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
        
        .status-available {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .status-unavailable {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.7rem;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: none;
            font-size: 0.8rem;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
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
        
        .room-types-section {
            background: rgba(30, 58, 95, 0.15);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
        }
        
        .room-types-section h3 {
            color: var(--accent);
            margin-bottom: 12px;
            font-size: 1rem;
        }
        
        .room-types-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .room-type-tag {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background: rgba(30, 58, 95, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .room-type-tag button {
            background: none;
            border: none;
            color: #f87171;
            cursor: pointer;
            margin-left: 6px;
            font-size: 1rem;
            line-height: 1;
            transition: color 0.3s ease;
        }
        
        .room-type-tag button:hover {
            color: #ef4444;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    
    <div class="rooms-container">
        <div class="page-header">
            <h1>Room Management</h1>
            <div style="display: flex; gap: 12px;">
                <button class="btn btn-back" onclick="window.location.href='<?php echo esc_url(home_url('/staydesk-dashboard')); ?>'">
                    <svg data-feather="arrow-left"></svg>
                    Dashboard
                </button>
                <button class="btn btn-primary" onclick="toggleForm()">
                    <svg data-feather="plus"></svg>
                    Add Room
                </button>
            </div>
        </div>
        
        <div id="alertBox" class="alert"></div>
        
        <!-- Room Types Management Section -->
        <div class="form-section" style="margin-bottom: 30px;">
            <h2>Manage Room Types</h2>
            <div style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;">
                <div class="form-group" style="flex: 1; margin: 0;">
                    <label>Add New Room Type</label>
                    <input type="text" id="newRoomType" placeholder="e.g., Executive Suite, Penthouse">
                </div>
                <button type="button" class="btn btn-primary" onclick="addRoomType()">+ Add Type</button>
            </div>
            <div id="roomTypesList" style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($room_types as $type): ?>
                    <span class="room-type-tag" data-id="<?php echo $type->id; ?>">
                        <?php echo esc_html($type->type_name); ?>
                        <?php if ($type->hotel_id): ?>
                            <button onclick="deleteRoomType(<?php echo $type->id; ?>)" style="background: none; border: none; color: #ff4444; cursor: pointer; margin-left: 8px;">×</button>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div id="roomForm" class="form-section">
            <h2>Add New Room</h2>
            <form id="addRoomForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Room Name *</label>
                        <input type="text" name="room_name" required>
                    </div>
                    <div class="form-group">
                        <label>Room Type *</label>
                        <select name="room_type" id="roomTypeSelect" required>
                            <option value="">Select Type</option>
                            <?php foreach ($room_types as $type): ?>
                                <option value="<?php echo esc_attr($type->type_name); ?>"><?php echo esc_html($type->type_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price per Night (₦) *</label>
                        <input type="number" name="price_per_night" required min="0" step="100">
                    </div>
                    <div class="form-group">
                        <label>Max Guests *</label>
                        <input type="number" name="max_guests" required min="1" max="10">
                    </div>
                    <div class="form-group">
                        <label>Bed Type</label>
                        <select name="bed_type">
                            <option value="Single">Single Bed</option>
                            <option value="Double">Double Bed</option>
                            <option value="Queen">Queen Bed</option>
                            <option value="King">King Bed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Size (sq ft)</label>
                        <input type="number" name="room_size" min="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Amenities (comma separated)</label>
                    <input type="text" name="amenities" placeholder="WiFi, TV, Air Conditioning, Mini Bar">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="availability_status">
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary">Save Room</button>
                    <button type="button" class="btn btn-back" onclick="toggleForm()">Cancel</button>
                </div>
            </form>
        </div>
        
        <div class="rooms-table">
            <?php if (empty($rooms)): ?>
                <div class="empty-state">
                    <h3>No Rooms Yet</h3>
                    <p>Click "Add New Room" to get started</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Room Name</th>
                            <th>Type</th>
                            <th>Price/Night</th>
                            <th>Max Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong><?php echo esc_html($room->room_name); ?></strong></td>
                                <td><?php echo esc_html($room->room_type); ?></td>
                                <td>₦<?php echo number_format($room->price_per_night, 0); ?></td>
                                <td><?php echo esc_html($room->max_guests); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $room->availability_status; ?>">
                                        <?php echo ucfirst($room->availability_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm" onclick="toggleStatus(<?php echo $room->id; ?>, '<?php echo $room->availability_status; ?>')">
                                            <?php echo $room->availability_status === 'available' ? 'Disable' : 'Enable'; ?>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteRoom(<?php echo $room->id; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleForm() {
            const form = document.getElementById('roomForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('addRoomForm').reset();
            }
        }
        
        function showAlert(message, type) {
            const alert = document.getElementById('alertBox');
            alert.className = 'alert alert-' + type + ' show';
            alert.textContent = message;
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
        
        jQuery(document).ready(function($) {
            $('#addRoomForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'staydesk_add_room',
                        nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                        hotel_id: <?php echo $hotel->id; ?>,
                        ...Object.fromEntries(new FormData(this))
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('Room added successfully!', 'success');
                            // Trigger dashboard update via localStorage
                            localStorage.setItem('staydesk_data_updated', Date.now());
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            showAlert(response.data.message || 'Error adding room', 'error');
                        }
                    },
                    error: function() {
                        showAlert('An error occurred. Please try again.', 'error');
                    }
                });
            });
        });
        
        function toggleStatus(roomId, currentStatus) {
            const newStatus = currentStatus === 'available' ? 'unavailable' : 'available';
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_update_room_status',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    room_id: roomId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Room status updated!', 'success');
                        // Trigger dashboard update via localStorage
                        localStorage.setItem('staydesk_data_updated', Date.now());
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('Error updating status', 'error');
                    }
                }
            });
        }
        
        function deleteRoom(roomId) {
            if (!confirm('Are you sure you want to delete this room?')) return;
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_delete_room',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    room_id: roomId
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Room deleted successfully!', 'success');
                        // Trigger dashboard update via localStorage
                        localStorage.setItem('staydesk_data_updated', Date.now());
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('Error deleting room', 'error');
                    }
                }
            });
        }
        
        function addRoomType() {
            const typeName = document.getElementById('newRoomType').value.trim();
            if (!typeName) {
                showAlert('Please enter a room type name', 'error');
                return;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_add_room_type',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    hotel_id: <?php echo $hotel->id; ?>,
                    type_name: typeName
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Room type added successfully!', 'success');
                        document.getElementById('newRoomType').value = '';
                        
                        // Add to list without reload
                        const tag = document.createElement('span');
                        tag.className = 'room-type-tag';
                        tag.setAttribute('data-id', response.data.type_id);
                        tag.innerHTML = typeName + '<button onclick="deleteRoomType(' + response.data.type_id + ')" style="background: none; border: none; color: #ff4444; cursor: pointer; margin-left: 8px;">×</button>';
                        document.getElementById('roomTypesList').appendChild(tag);
                        
                        // Add to dropdown
                        const option = document.createElement('option');
                        option.value = typeName;
                        option.textContent = typeName;
                        document.getElementById('roomTypeSelect').appendChild(option);
                    } else {
                        showAlert(response.data.message || 'Error adding room type', 'error');
                    }
                }
            });
        }
        
        function deleteRoomType(typeId) {
            if (!confirm('Are you sure you want to delete this room type?')) return;
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_delete_room_type',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    type_id: typeId
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Room type deleted successfully!', 'success');
                        
                        // Remove from list without reload
                        const tag = document.querySelector('.room-type-tag[data-id="' + typeId + '"]');
                        if (tag) tag.remove();
                        
                        // Remove from dropdown
                        const options = document.getElementById('roomTypeSelect').options;
                        for (let i = 0; i < options.length; i++) {
                            if (options[i].value === response.data.type_name) {
                                options[i].remove();
                                break;
                            }
                        }
                    } else {
                        showAlert('Error deleting room type', 'error');
                    }
                }
            });
        }
        
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
</body>
</html>