<?php
/**
 * AI Chatbot Widget with Settings & Document Training
 * Rebuilt from scratch with PDF/DOC upload capability
 */

// Get hotel data for context
global $wpdb;
$hotel = null;
$hotel_id = 0;
$training_documents = array();

if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $table_hotels = $wpdb->prefix . 'staydesk_hotels';
    $hotel = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_hotels WHERE user_id = %d",
        $user_id
    ));
    
    if ($hotel) {
        $hotel_id = $hotel->id;
        
        // Get uploaded training documents
        $table_docs = $wpdb->prefix . 'staydesk_training_documents';
        $training_documents = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_docs WHERE hotel_id = %d ORDER BY uploaded_at DESC",
            $hotel_id
        ));
    }
}

$is_owner = $hotel && is_user_logged_in();
?>

<!-- AI Chatbot Widget with Settings -->
<div class="staydesk-chatbot-widget" id="staydesk-chatbot-widget">
    <button class="chatbot-button" id="chatbot-toggle" title="Chat with AI Assistant">
        <!-- Robot/Chatbot Icon - Filled for better visibility -->
        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
            <!-- Robot head -->
            <path d="M12 2a1 1 0 011 1v1h3a3 3 0 013 3v8a3 3 0 01-3 3H8a3 3 0 01-3-3V7a3 3 0 013-3h3V3a1 1 0 011-1z"/>
            <!-- Left antenna -->
            <circle cx="3" cy="11" r="1.5"/>
            <rect x="4" y="10" width="1" height="2"/>
            <!-- Right antenna -->
            <circle cx="21" cy="11" r="1.5"/>
            <rect x="19" y="10" width="1" height="2"/>
            <!-- Eyes (cutout effect using dark circles) -->
            <circle cx="9" cy="11" r="2" fill="#1e3a5f"/>
            <circle cx="15" cy="11" r="2" fill="#1e3a5f"/>
            <!-- Eye pupils/highlights -->
            <circle cx="9" cy="11" r="1" fill="white"/>
            <circle cx="15" cy="11" r="1" fill="white"/>
            <!-- Smile -->
            <path d="M9 14.5c0 0 1.5 1.5 3 1.5s3-1.5 3-1.5" stroke="#1e3a5f" stroke-width="1.5" stroke-linecap="round" fill="none"/>
        </svg>
    </button>
    
    <div class="chatbot-window" id="chatbot-window">
        <!-- Main Chat View -->
        <div class="chat-view" id="chat-view">
            <div class="chat-header">
                <div class="header-content">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="margin-right: 8px;">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    <div>
                        <strong>AI Assistant</strong>
                        <small>Trained & Ready</small>
                    </div>
                </div>
                <div class="header-actions">
                    <?php if ($is_owner): ?>
                    <button class="settings-btn" id="settings-btn" title="Settings">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                        </svg>
                    </button>
                    <?php endif; ?>
                    <button class="minimize-chat" id="minimize-chat" title="Minimize">−</button>
                </div>
            </div>
            
            <div class="chat-body" id="chat-body">
                <div class="chat-message bot-message">
                    <p>👋 Hello! I'm your AI assistant trained specifically for this hotel.</p>
                    <p style="margin-top: 8px; font-size: 0.8rem;">I've been trained on custom documents to provide accurate, human-like responses. Ask me anything!</p>
                </div>
            </div>
            
            <div class="chat-footer">
                <input type="text" id="chat-input" placeholder="Ask me anything..." />
                <button class="send-btn" id="send-btn">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Settings View -->
        <div class="settings-view" id="settings-view" style="display: none;">
            <div class="chat-header settings-header">
                <div class="header-content">
                    <button class="back-btn" id="back-to-chat" title="Back to Chat">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                        </svg>
                    </button>
                    <div>
                        <strong>Chatbot Settings</strong>
                        <small>Train your AI</small>
                    </div>
                </div>
            </div>
            
            <div class="settings-body">
                <div class="settings-section">
                    <h3>📚 Training Documents</h3>
                    <p class="settings-desc">Upload PDF or DOC files to train your chatbot. The AI will learn from these documents to provide better, human-like responses.</p>
                    
                    <div class="upload-area" id="upload-area">
                        <input type="file" id="document-upload" accept=".pdf,.doc,.docx" multiple style="display: none;">
                        <div class="upload-icon">📁</div>
                        <p>Drag & drop files here or <span class="upload-link">browse</span></p>
                        <small>Supports: PDF, DOC, DOCX (Max 10MB each)</small>
                    </div>
                    
                    <div class="upload-progress" id="upload-progress" style="display: none;">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill"></div>
                        </div>
                        <span id="progress-text">Uploading...</span>
                    </div>
                    
                    <div class="documents-list" id="documents-list">
                        <h4>Uploaded Documents</h4>
                        <?php if (!empty($training_documents)): ?>
                            <?php foreach ($training_documents as $doc): ?>
                            <div class="document-item" data-id="<?php echo esc_attr($doc->id); ?>">
                                <div class="doc-info">
                                    <span class="doc-icon"><?php echo $doc->file_type === 'pdf' ? '📄' : '📝'; ?></span>
                                    <div>
                                        <span class="doc-name"><?php echo esc_html($doc->file_name); ?></span>
                                        <small class="doc-date"><?php echo date('M j, Y', strtotime($doc->uploaded_at)); ?></small>
                                    </div>
                                </div>
                                <div class="doc-actions">
                                    <span class="doc-status <?php echo $doc->is_processed ? 'processed' : 'pending'; ?>">
                                        <?php echo $doc->is_processed ? '✓ Trained' : '⏳ Processing'; ?>
                                    </span>
                                    <button class="delete-doc-btn" data-id="<?php echo esc_attr($doc->id); ?>" title="Delete">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-documents">
                                <p>No documents uploaded yet.</p>
                                <small>Upload documents to train your AI assistant.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>⚙️ Response Settings</h3>
                    
                    <div class="setting-item">
                        <label for="response-tone">Response Tone</label>
                        <select id="response-tone" class="setting-select">
                            <option value="friendly">Friendly & Casual</option>
                            <option value="professional">Professional</option>
                            <option value="warm">Warm & Welcoming</option>
                        </select>
                    </div>
                    
                    <div class="setting-item">
                        <label for="response-length">Response Length</label>
                        <select id="response-length" class="setting-select">
                            <option value="concise">Concise</option>
                            <option value="detailed">Detailed</option>
                            <option value="balanced" selected>Balanced</option>
                        </select>
                    </div>
                </div>
                
                <button class="save-settings-btn" id="save-settings-btn">💾 Save Settings</button>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Support Widget -->
<div class="staydesk-whatsapp-widget" id="staydesk-whatsapp-widget">
    <a href="https://wa.me/2347120018023" target="_blank" class="whatsapp-button" title="WhatsApp Support">
        <svg viewBox="0 0 32 32" width="20" height="20">
            <path fill="currentColor" d="M16 0c-8.837 0-16 7.163-16 16 0 2.825 0.737 5.607 2.137 8.048l-2.137 7.952 8.135-2.135c2.369 1.313 5.061 2.010 7.865 2.010 8.837 0 16-7.163 16-16s-7.163-16-16-16zM16 29.333c-2.547 0-5.033-0.727-7.193-2.101l-0.509-0.311-5.285 1.387 1.408-5.245-0.341-0.528c-1.515-2.344-2.315-5.053-2.315-7.869 0-7.364 5.991-13.355 13.355-13.355s13.355 5.991 13.355 13.355-5.991 13.355-13.355 13.355zM23.197 19.484c-0.389-0.195-2.299-1.137-2.656-1.267s-0.616-0.195-0.875 0.195c-0.259 0.389-1.005 1.267-1.232 1.527s-0.453 0.292-0.843 0.097c-0.389-0.195-1.643-0.605-3.129-1.932-1.157-1.032-1.939-2.308-2.165-2.697s-0.024-0.6 0.171-0.795c0.176-0.176 0.389-0.453 0.584-0.681s0.259-0.389 0.389-0.648c0.129-0.259 0.065-0.487-0.032-0.681s-0.875-2.109-1.199-2.889c-0.316-0.759-0.637-0.656-0.875-0.669-0.227-0.013-0.487-0.016-0.747-0.016s-0.681 0.097-1.037 0.487c-0.357 0.389-1.364 1.333-1.364 3.249s1.397 3.768 1.591 4.027c0.195 0.259 2.749 4.199 6.659 5.884 0.931 0.403 1.657 0.643 2.223 0.823 0.935 0.297 1.785 0.255 2.457 0.155 0.749-0.112 2.299-0.939 2.624-1.845s0.325-1.683 0.227-1.845c-0.097-0.163-0.357-0.259-0.747-0.453z"/>
        </svg>
    </a>
</div>

<style>
    /* AI Chatbot Widget Styles */
    .staydesk-chatbot-widget {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 9998;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .chatbot-button {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 50%, #6bb3ff 100%);
        background-size: 200% 200%;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(107, 179, 255, 0.4);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        animation: sapphireGlow 3s ease infinite;
        position: relative;
        overflow: hidden;
    }
    
    .chatbot-button::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(107, 179, 255, 0.4), transparent);
        transform: rotate(45deg);
        animation: shimmer 3s infinite;
    }
    
    /* Sparkle effects */
    .chatbot-button::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.3) 0%, transparent 50%);
        animation: sparkle 2s ease-in-out infinite;
    }
    
    @keyframes sapphireGlow {
        0%, 100% { 
            background-position: 0% 50%; 
            box-shadow: 0 4px 20px rgba(107, 179, 255, 0.4);
        }
        50% { 
            background-position: 100% 50%; 
            box-shadow: 0 4px 30px rgba(107, 179, 255, 0.6), 0 0 40px rgba(107, 179, 255, 0.3);
        }
    }
    
    @keyframes sparkle {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.1); }
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .chatbot-button:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 30px rgba(107, 179, 255, 0.7), 0 0 50px rgba(107, 179, 255, 0.4);
    }
    
    .chatbot-window {
        position: absolute;
        bottom: 70px;
        left: 0;
        width: 360px;
        max-width: 90vw;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        display: none;
        animation: slideUp 0.3s ease-out;
    }
    
    .chatbot-window.minimized {
        height: 50px;
        overflow: hidden;
    }
    
    .chatbot-window.minimized .chat-body,
    .chatbot-window.minimized .chat-footer,
    .chatbot-window.minimized .settings-view {
        display: none;
    }
    
    .chat-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 50%, #6bb3ff 100%);
        background-size: 200% 200%;
        animation: sapphireHeaderGlow 4s ease infinite;
        color: white;
        padding: 12px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    @keyframes sapphireHeaderGlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .settings-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
    }
    
    .header-content {
        display: flex;
        align-items: center;
    }
    
    .header-content strong {
        display: block;
        font-size: 0.9rem;
    }
    
    .header-content small {
        font-size: 0.75rem;
        opacity: 0.9;
        display: block;
    }
    
    .header-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .settings-btn, .minimize-chat, .back-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .minimize-chat {
        font-size: 1.5rem;
        width: 25px;
        height: 25px;
        line-height: 1;
    }
    
    .settings-btn:hover, .minimize-chat:hover, .back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .back-btn {
        margin-right: 8px;
    }
    
    .chat-body {
        padding: 15px;
        background: #f5f5f5;
        height: 280px;
        overflow-y: auto;
    }
    
    .chat-message {
        margin-bottom: 12px;
        animation: fadeIn 0.3s ease-out;
    }
    
    .bot-message {
        background: white;
        padding: 10px 12px;
        border-radius: 12px 12px 12px 0;
        max-width: 85%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .bot-message p {
        margin: 0;
        color: #333;
        line-height: 1.5;
        font-size: 0.85rem;
    }
    
    .user-message {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
        color: white;
        padding: 10px 12px;
        border-radius: 12px 12px 0 12px;
        max-width: 85%;
        margin-left: auto;
        text-align: right;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .user-message p {
        margin: 0;
        font-size: 0.85rem;
    }
    
    .chat-footer {
        padding: 12px;
        background: white;
        border-top: 1px solid #e0e0e0;
        display: flex;
        gap: 8px;
    }
    
    #chat-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 20px;
        font-size: 0.85rem;
        outline: none;
        transition: border-color 0.2s;
    }
    
    #chat-input:focus {
        border-color: #4FC3F7;
    }
    
    .send-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .send-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(107, 179, 255, 0.4);
    }
    
    .send-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Settings View Styles */
    .settings-body {
        padding: 15px;
        background: #1a1a1a;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .settings-section {
        background: #2a2a2a;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }
    
    .settings-section h3 {
        margin: 0 0 10px 0;
        color: #FFD700;
        font-size: 1rem;
    }
    
    .settings-desc {
        color: #aaa;
        font-size: 0.8rem;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .upload-area {
        border: 2px dashed rgba(212, 175, 55, 0.4);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: rgba(42, 42, 42, 0.5);
    }
    
    .upload-area:hover, .upload-area.dragover {
        border-color: #FFD700;
        background: rgba(212, 175, 55, 0.1);
    }
    
    .upload-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .upload-area p {
        color: #ccc;
        margin: 0 0 5px 0;
        font-size: 0.9rem;
    }
    
    .upload-link {
        color: #FFD700;
        cursor: pointer;
    }
    
    .upload-area small {
        color: #888;
        font-size: 0.75rem;
    }
    
    .upload-progress {
        margin-top: 15px;
    }
    
    .progress-bar {
        background: #333;
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
    }
    
    .progress-fill {
        background: linear-gradient(90deg, #FFD700, #4FC3F7);
        height: 100%;
        width: 0%;
        transition: width 0.3s;
    }
    
    #progress-text {
        color: #aaa;
        font-size: 0.8rem;
        display: block;
        margin-top: 5px;
    }
    
    .documents-list {
        margin-top: 15px;
    }
    
    .documents-list h4 {
        color: #ccc;
        font-size: 0.85rem;
        margin: 0 0 10px 0;
        font-weight: 600;
    }
    
    .document-item {
        background: rgba(42, 42, 42, 0.8);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .doc-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .doc-icon {
        font-size: 1.5rem;
    }
    
    .doc-name {
        color: #fff;
        font-size: 0.85rem;
        display: block;
    }
    
    .doc-date {
        color: #888;
        font-size: 0.7rem;
    }
    
    .doc-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .doc-status {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 10px;
    }
    
    .doc-status.processed {
        background: rgba(40, 167, 69, 0.2);
        color: #4ADE80;
    }
    
    .doc-status.pending {
        background: rgba(255, 193, 7, 0.2);
        color: #FFC107;
    }
    
    .delete-doc-btn {
        background: none;
        border: none;
        color: #ff6b6b;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    
    .delete-doc-btn:hover {
        background: rgba(255, 107, 107, 0.2);
    }
    
    .no-documents {
        text-align: center;
        padding: 20px;
        color: #888;
    }
    
    .no-documents p {
        margin: 0 0 5px 0;
        font-size: 0.9rem;
    }
    
    .no-documents small {
        font-size: 0.75rem;
    }
    
    .setting-item {
        margin-bottom: 12px;
    }
    
    .setting-item label {
        display: block;
        color: #ccc;
        font-size: 0.85rem;
        margin-bottom: 5px;
    }
    
    .setting-select {
        width: 100%;
        padding: 10px;
        background: #333;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #fff;
        font-size: 0.85rem;
        outline: none;
        cursor: pointer;
    }
    
    .setting-select:focus {
        border-color: #FFD700;
    }
    
    .save-settings-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
        border: none;
        border-radius: 10px;
        color: #000;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .save-settings-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
    }
    
    /* WhatsApp Widget - Sparkling Sapphire Blue */
    .staydesk-whatsapp-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }
    
    .whatsapp-button {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 50%, #6bb3ff 100%);
        background-size: 200% 200%;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(107, 179, 255, 0.4);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        animation: whatsappSapphireGlow 3s ease infinite;
        position: relative;
        overflow: hidden;
    }
    
    .whatsapp-button::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(107, 179, 255, 0.4), transparent);
        transform: rotate(45deg);
        animation: whatsappShimmer 3s infinite;
    }
    
    .whatsapp-button::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.3) 0%, transparent 50%);
        animation: whatsappSparkle 2s ease-in-out infinite;
    }
    
    @keyframes whatsappSapphireGlow {
        0%, 100% { 
            background-position: 0% 50%; 
            box-shadow: 0 4px 20px rgba(107, 179, 255, 0.4);
        }
        50% { 
            background-position: 100% 50%; 
            box-shadow: 0 4px 30px rgba(107, 179, 255, 0.6), 0 0 40px rgba(107, 179, 255, 0.3);
        }
    }
    
    @keyframes whatsappShimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    @keyframes whatsappSparkle {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.1); }
    }
    
    .whatsapp-button:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(107, 179, 255, 0.7), 0 0 50px rgba(107, 179, 255, 0.4);
    }
    
    .whatsapp-button svg {
        position: relative;
        z-index: 1;
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 10px 12px;
    }
    
    .typing-indicator span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #999;
        animation: typingBounce 1.4s infinite;
    }
    
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-10px); }
    }
    
    @media (max-width: 768px) {
        .staydesk-chatbot-widget {
            bottom: 15px;
            left: 15px;
        }
        
        .staydesk-whatsapp-widget {
            bottom: 15px;
            right: 15px;
        }
        
        .chatbot-button {
            width: 50px;
            height: 50px;
        }
        
        .whatsapp-button {
            width: 45px;
            height: 45px;
        }
        
        .chatbot-window {
            width: calc(100vw - 30px);
            left: 0;
        }
    }
</style>

<script>
(function() {
    var chatToggle = document.getElementById('chatbot-toggle');
    var chatWindow = document.getElementById('chatbot-window');
    var minimizeBtn = document.getElementById('minimize-chat');
    var sendBtn = document.getElementById('send-btn');
    var chatInput = document.getElementById('chat-input');
    var chatBody = document.getElementById('chat-body');
    var settingsBtn = document.getElementById('settings-btn');
    var backToChat = document.getElementById('back-to-chat');
    var chatView = document.getElementById('chat-view');
    var settingsView = document.getElementById('settings-view');
    var uploadArea = document.getElementById('upload-area');
    var documentUpload = document.getElementById('document-upload');
    var saveSettingsBtn = document.getElementById('save-settings-btn');
    
    var hotelId = <?php echo $hotel_id; ?>;
    var sessionId = 'session_' + Date.now();
    var isOwner = <?php echo $is_owner ? 'true' : 'false'; ?>;
    
    // Toggle chatbot window
    if (chatToggle && chatWindow) {
        chatToggle.addEventListener('click', function() {
            if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
                chatWindow.style.display = 'block';
                chatWindow.classList.remove('minimized');
            } else {
                chatWindow.style.display = 'none';
            }
        });
    }
    
    // Minimize/Maximize chatbot
    if (minimizeBtn) {
        minimizeBtn.addEventListener('click', function() {
            chatWindow.classList.toggle('minimized');
            minimizeBtn.textContent = chatWindow.classList.contains('minimized') ? '+' : '−';
        });
    }
    
    // Settings toggle
    if (settingsBtn && isOwner) {
        settingsBtn.addEventListener('click', function() {
            chatView.style.display = 'none';
            settingsView.style.display = 'block';
        });
    }
    
    if (backToChat) {
        backToChat.addEventListener('click', function() {
            settingsView.style.display = 'none';
            chatView.style.display = 'block';
        });
    }
    
    // File upload handling
    if (uploadArea && documentUpload) {
        uploadArea.addEventListener('click', function() {
            documentUpload.click();
        });
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function() {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });
        
        documentUpload.addEventListener('change', function() {
            handleFiles(this.files);
        });
    }
    
    function handleFiles(files) {
        if (!files.length) return;
        
        var progressDiv = document.getElementById('upload-progress');
        var progressFill = document.getElementById('progress-fill');
        var progressText = document.getElementById('progress-text');
        
        progressDiv.style.display = 'block';
        
        for (var i = 0; i < files.length; i++) {
            uploadFile(files[i], progressFill, progressText);
        }
    }
    
    function uploadFile(file, progressFill, progressText) {
        var formData = new FormData();
        formData.append('action', 'staydesk_upload_training_document');
        formData.append('hotel_id', hotelId);
        formData.append('document', file);
        formData.append('nonce', '<?php echo wp_create_nonce('staydesk_nonce'); ?>');
        
        var xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var percent = Math.round((e.loaded / e.total) * 100);
                progressFill.style.width = percent + '%';
                progressText.textContent = 'Uploading ' + file.name + '... ' + percent + '%';
            }
        });
        
        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    progressText.textContent = 'Processing document...';
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    progressText.textContent = 'Error: ' + (response.data || 'Upload failed');
                }
            }
        });
        
        xhr.addEventListener('error', function() {
            progressText.textContent = 'Upload failed. Please try again.';
        });
        
        xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
        xhr.send(formData);
    }
    
    // Delete document handling
    document.querySelectorAll('.delete-doc-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var docId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this document?')) {
                deleteDocument(docId, this.closest('.document-item'));
            }
        });
    });
    
    function deleteDocument(docId, element) {
        if (typeof jQuery !== 'undefined') {
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_delete_training_document',
                    document_id: docId,
                    hotel_id: hotelId,
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        element.remove();
                    } else {
                        alert('Failed to delete document: ' + (response.data || 'Unknown error'));
                    }
                }
            });
        }
    }
    
    // Save settings
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', function() {
            var tone = document.getElementById('response-tone').value;
            var length = document.getElementById('response-length').value;
            
            if (typeof jQuery !== 'undefined') {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'staydesk_save_chatbot_settings',
                        hotel_id: hotelId,
                        response_tone: tone,
                        response_length: length,
                        nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Settings saved successfully!');
                        } else {
                            alert('Failed to save settings.');
                        }
                    }
                });
            }
        });
    }
    
    // Send message
    function sendMessage() {
        var message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        var userMsg = document.createElement('div');
        userMsg.className = 'chat-message user-message';
        userMsg.innerHTML = '<p>' + escapeHtml(message) + '</p>';
        chatBody.appendChild(userMsg);
        
        chatInput.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;
        sendBtn.disabled = true;
        
        // Typing indicator
        var typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message bot-message typing-indicator';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        chatBody.appendChild(typingDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
        
        // Send to AI backend
        if (typeof jQuery !== 'undefined') {
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_chatbot_message',
                    hotel_id: hotelId,
                    session_id: sessionId,
                    message: message,
                    language: 'en',
                    use_training_data: true
                },
                success: function(response) {
                    typingDiv.remove();
                    
                    var botMsg = document.createElement('div');
                    botMsg.className = 'chat-message bot-message';
                    
                    if (response.success && response.data) {
                        botMsg.innerHTML = '<p>' + (response.data.message || 'I can help you with that!') + '</p>';
                    } else {
                        botMsg.innerHTML = '<p>Sorry, I encountered an error. Please try again.</p>';
                    }
                    
                    chatBody.appendChild(botMsg);
                    chatBody.scrollTop = chatBody.scrollHeight;
                    sendBtn.disabled = false;
                    chatInput.focus();
                },
                error: function() {
                    typingDiv.remove();
                    
                    var errorMsg = document.createElement('div');
                    errorMsg.className = 'chat-message bot-message';
                    errorMsg.innerHTML = '<p>Sorry, I\'m having trouble connecting. Please try again.</p>';
                    chatBody.appendChild(errorMsg);
                    
                    chatBody.scrollTop = chatBody.scrollHeight;
                    sendBtn.disabled = false;
                    chatInput.focus();
                }
            });
        }
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }
    
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }
})();
</script>