@extends('frontend.layouts.master')

@section('title', 'Chat - PangAIaShop')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>AI Chat Assistant</h4>
                </div>
                <div class="card-body">
                    <div id="chat-messages" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;">
                        <div class="alert alert-info">
                            Welcome! I'm your AI assistant for PangAIaShop. How can I help you today?
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="text" id="message-input" class="form-control" placeholder="Type your message..." maxlength="1000">
                        <button class="btn btn-primary" id="send-button">Send</button>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-secondary btn-sm" id="clear-button">Clear Chat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let conversation = [];
const messagesDiv = document.getElementById('chat-messages');
const messageInput = document.getElementById('message-input');
const sendButton = document.getElementById('send-button');
const clearButton = document.getElementById('clear-button');

function addMessage(content, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `mb-3 ${isUser ? 'text-end' : 'text-start'}`;
    
    const badgeClass = isUser ? 'bg-primary' : 'bg-success';
    const label = isUser ? 'You' : 'AI Assistant';
    
    messageDiv.innerHTML = `
        <div class="d-inline-block">
            <span class="badge ${badgeClass} mb-1">${label}</span>
            <div class="card ${isUser ? 'bg-light' : 'bg-white'}" style="max-width: 70%;">
                <div class="card-body p-2">
                    ${content}
                </div>
            </div>
        </div>
    `;
    
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function showError(message, debug = null) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger mb-3';
    errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
    
    if (debug && typeof debug === 'string') {
        errorDiv.innerHTML += `<br><small class="text-muted">${debug}</small>`;
    }
    
    messagesDiv.appendChild(errorDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

async function sendMessage() {
    const message = messageInput.value.trim();
    if (!message) return;

    // Disable send button
    sendButton.disabled = true;
    sendButton.textContent = 'Sending...';

    addMessage(message, true);
    messageInput.value = '';

    try {
        const response = await fetch('{{ route("chat.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                conversation: conversation
            })
        });

        const data = await response.json();
        
        if (data.success) {
            addMessage(data.message);
            conversation = data.conversation || [];
        } else {
            showError(data.error || 'Unknown error occurred', data.debug);
        }
    } catch (error) {
        showError('Network error: ' + error.message);
        console.error('Chat error:', error);
    } finally {
        // Re-enable send button
        sendButton.disabled = false;
        sendButton.textContent = 'Send';
    }
}

function clearChat() {
    if (confirm('Are you sure you want to clear the chat?')) {
        conversation = [];
        messagesDiv.innerHTML = '<div class="alert alert-info">Chat cleared. How can I help you?</div>';
        
        // Call the clear endpoint
        fetch('{{ route("chat.clear") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
    }
}

// Event listeners
messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

sendButton.addEventListener('click', sendMessage);
clearButton.addEventListener('click', clearChat);

// Focus on input
messageInput.focus();
</script>
@endpush
@endsection
