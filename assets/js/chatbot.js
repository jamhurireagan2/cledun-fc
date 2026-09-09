// CLEDUN FC - AI Chatbot
const chatbot = {
    isOpen: false,
    messages: [],
    
    // Knowledge base
    knowledge: {
        'hello': 'Hello! Welcome to CLEDUN FC! How can I help you today? ⚽',
        'hi': 'Hi there! Welcome to CLEDUN FC! What would you like to know?',
        'team': 'CLEDUN FC has 5 teams: U8, U10, U13, U17, and Senior. Each team develops young talent and builds champions!',
        'teams': 'CLEDUN FC has 5 teams: U8, U10, U13, U17, and Senior. Each team develops young talent and builds champions!',
        'vision': 'Our vision is to participate in world youth football tournaments globally, both as a team and through individual player representation.',
        'mission': 'Our mission is to nurture and foster young talent while building self-confidence, discipline, and character in young athletes.',
        'location': 'We are based at Farasi Lane Primary School. Come visit us! 🏟️',
        'address': 'We are based at Farasi Lane Primary School. Come visit us! 🏟️',
        'training': 'We train with a focus on ball mastery, speed & agility, positional play, strength & conditioning, cardio & plyometrics.',
        'matches': 'Check our Matches page for upcoming fixtures and results! ⚽',
        'tickets': 'You can buy tickets on our Tickets page. See you at the stadium! 🎟️',
        'contact': 'You can reach us via email at info@cledunfc.com or call us at +254 700 123 456.',
        'email': 'Our email is info@cledunfc.com',
        'phone': 'You can call us at +254 700 123 456',
        'academy': 'Our academy develops young talent from age 6, focusing on holistic player development including physical, mental, and emotional growth.',
        'sponsor': 'We welcome sponsorships! Contact us for partnership opportunities.',
        'sponsorship': 'We welcome sponsorships! Contact us for partnership opportunities.',
        'goals': 'Our long-term goals: Norway Cup 2030 (U10 & U12), Gothia Cup 2031 (U15), Own facility by 2035 with players in national teams!',
        'history': 'CLEDUN FC was established in 2026 with a mission to build champions.',
        'established': 'CLEDUN FC was established in 2026.',
        'help': 'I can help you with: Teams, Vision, Mission, Location, Training, Matches, Tickets, Contact, Academy, Sponsorship, and more!',
        'thanks': 'You\'re welcome! ⚽ Come watch our matches and support CLEDUN FC! 🏆',
        'thank you': 'You\'re welcome! ⚽ Come watch our matches and support CLEDUN FC! 🏆',
        'bye': 'Goodbye! ⚽ Come back soon! Visit cledunfc.com for more info.',
        'goodbye': 'Goodbye! ⚽ Come back soon! Visit cledunfc.com for more info.'
    },
    
    getResponse(message) {
        const msg = message.toLowerCase().trim();
        
        // Check for exact matches
        for (let [key, response] of Object.entries(this.knowledge)) {
            if (msg.includes(key)) {
                return response;
            }
        }
        
        // Check for keywords
        if (msg.includes('u8') || msg.includes('u10') || msg.includes('u13') || msg.includes('u17')) {
            return 'CLEDUN FC has development teams for all ages: U8, U10, U13, U17, and Senior. Each team has dedicated coaches and training programs! ⚽';
        }
        
        if (msg.includes('coach') || msg.includes('trainer')) {
            return 'Our coaching staff includes experienced professionals dedicated to developing young talent. Check our Staff page to meet the team! 👨‍🏫';
        }
        
        if (msg.includes('player') || msg.includes('squad')) {
            return 'We have talented players across all age groups. Check our Squad page to see our players! 👥';
        }
        
        if (msg.includes('match') || msg.includes('fixture') || msg.includes('game')) {
            return 'Check our Matches page for upcoming fixtures and results! We play at Farasi Lane Stadium. 🏟️';
        }
        
        if (msg.includes('shop') || msg.includes('merchandise') || msg.includes('merch')) {
            return 'We\'re working on our merchandise store! Stay tuned for CLEDUN FC kits and merchandise. 🛍️';
        }
        
        return 'I\'m not sure about that. Please ask about: Teams, Vision, Mission, Location, Training, Matches, Tickets, Contact, Academy, Sponsorship, Goals, or History. Or you can email us at info@cledunfc.com! ⚽';
    },
    
    addMessage(sender, text) {
        this.messages.push({ sender, text, time: new Date().toLocaleTimeString() });
        this.renderMessages();
    },
    
    renderMessages() {
        const container = document.getElementById('chatMessages');
        if (!container) return;
        
        container.innerHTML = '';
        this.messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = `chat-message ${msg.sender}`;
            div.innerHTML = `
                <div class="message-bubble">
                    <span class="message-text">${msg.text}</span>
                    <span class="message-time">${msg.time}</span>
                </div>
            `;
            container.appendChild(div);
        });
        container.scrollTop = container.scrollHeight;
    },
    
    sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;
        
        this.addMessage('user', message);
        input.value = '';
        
        // Show typing indicator
        this.showTyping();
        
        // Simulate AI thinking
        setTimeout(() => {
            this.hideTyping();
            const response = this.getResponse(message);
            this.addMessage('bot', response);
        }, 1000 + Math.random() * 500);
    },
    
    showTyping() {
        const container = document.getElementById('chatMessages');
        const typing = document.createElement('div');
        typing.className = 'chat-message bot typing';
        typing.id = 'typingIndicator';
        typing.innerHTML = `
            <div class="message-bubble">
                <span class="typing-dots">
                    <span></span><span></span><span></span>
                </span>
            </div>
        `;
        container.appendChild(typing);
        container.scrollTop = container.scrollHeight;
    },
    
    hideTyping() {
        const typing = document.getElementById('typingIndicator');
        if (typing) typing.remove();
    },
    
    toggle() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('chatWindow');
        const chatButton = document.getElementById('chatButton');
        
        if (this.isOpen) {
            chatWindow.classList.add('open');
            chatButton.style.display = 'none';
            if (this.messages.length === 0) {
                this.addMessage('bot', 'Hello! 👋 I\'m your CLEDUN FC assistant. Ask me anything about the club!');
                setTimeout(() => {
                    this.addMessage('bot', 'Try asking: "What teams do you have?" or "What is your vision?" ⚽');
                }, 2000);
            }
        } else {
            chatWindow.classList.remove('open');
            chatButton.style.display = 'flex';
        }
    }
};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Create chat HTML
    const chatHTML = `
        <style>
            /* Chat Button */
            .chat-button {
                position: fixed;
                bottom: 100px;
                right: 30px;
                z-index: 9998;
                background: linear-gradient(135deg, #1a2a6c, #2a3f8a);
                color: white;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(26, 42, 108, 0.4);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
            }
            
            .chat-button:hover {
                transform: scale(1.1);
                box-shadow: 0 8px 30px rgba(26, 42, 108, 0.6);
            }
            
            .chat-button .badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ef4444;
                color: white;
                border-radius: 50%;
                width: 22px;
                height: 22px;
                font-size: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulse-badge 2s infinite;
            }
            
            @keyframes pulse-badge {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            
            /* Chat Window */
            .chat-window {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 380px;
                max-width: calc(100vw - 40px);
                max-height: 550px;
                height: 500px;
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.2);
                z-index: 9999;
                display: none;
                flex-direction: column;
                overflow: hidden;
                animation: slideUp 0.3s ease;
            }
            
            .chat-window.open {
                display: flex;
            }
            
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .chat-header {
                background: linear-gradient(135deg, #1a2a6c, #2a3f8a);
                color: white;
                padding: 16px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-shrink: 0;
            }
            
            .chat-header h3 {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 700;
            }
            
            .chat-header h3 i {
                color: #fbbf24;
            }
            
            .chat-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.3rem;
                cursor: pointer;
                opacity: 0.8;
                transition: opacity 0.3s;
            }
            
            .chat-close:hover {
                opacity: 1;
            }
            
            .chat-messages {
                flex: 1;
                padding: 16px;
                overflow-y: auto;
                background: #f8fafc;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .chat-message {
                max-width: 85%;
                animation: fadeIn 0.3s ease;
            }
            
            .chat-message.user {
                align-self: flex-end;
            }
            
            .chat-message.bot {
                align-self: flex-start;
            }
            
            .message-bubble {
                background: white;
                padding: 10px 14px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                position: relative;
            }
            
            .chat-message.user .message-bubble {
                background: #1a2a6c;
                color: white;
                border-bottom-right-radius: 4px;
            }
            
            .chat-message.bot .message-bubble {
                background: white;
                color: #1a2a6c;
                border-bottom-left-radius: 4px;
                border: 1px solid #e5e7eb;
            }
            
            .message-text {
                font-size: 0.95rem;
                line-height: 1.5;
            }
            
            .message-time {
                font-size: 0.65rem;
                opacity: 0.6;
                display: block;
                margin-top: 4px;
                text-align: right;
            }
            
            .chat-message.user .message-time {
                color: rgba(255,255,255,0.7);
            }
            
            .chat-message.bot .message-time {
                color: #6b7280;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            /* Typing Dots */
            .typing-dots {
                display: flex;
                gap: 4px;
                padding: 4px 0;
            }
            
            .typing-dots span {
                width: 8px;
                height: 8px;
                background: #6b7280;
                border-radius: 50%;
                animation: typing 1.4s infinite;
            }
            
            .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
            .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
            
            @keyframes typing {
                0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
                30% { transform: translateY(-8px); opacity: 1; }
            }
            
            .chat-footer {
                padding: 12px 16px;
                border-top: 1px solid #e5e7eb;
                display: flex;
                gap: 10px;
                flex-shrink: 0;
                background: white;
            }
            
            .chat-footer input {
                flex: 1;
                padding: 10px 14px;
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                font-size: 0.95rem;
                font-family: inherit;
                transition: border-color 0.3s;
            }
            
            .chat-footer input:focus {
                outline: none;
                border-color: #fbbf24;
                box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.1);
            }
            
            .chat-footer button {
                padding: 10px 20px;
                background: #fbbf24;
                color: #1a2a6c;
                border: none;
                border-radius: 10px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s;
                font-family: inherit;
            }
            
            .chat-footer button:hover {
                background: #fcd34d;
                transform: translateY(-2px);
            }
            
            .chat-suggestions {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                padding: 8px 16px 12px;
                background: #f8fafc;
                flex-shrink: 0;
                border-top: 1px solid #e5e7eb;
            }
            
            .chat-suggestions button {
                padding: 4px 12px;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 20px;
                font-size: 0.75rem;
                cursor: pointer;
                transition: all 0.3s;
                font-family: inherit;
                color: #1a2a6c;
            }
            
            .chat-suggestions button:hover {
                background: #fbbf24;
                border-color: #fbbf24;
                transform: scale(1.05);
            }
            
            @media (max-width: 480px) {
                .chat-window {
                    bottom: 10px;
                    right: 10px;
                    height: 420px;
                    max-height: 70vh;
                }
                .chat-button {
                    bottom: 80px;
                    right: 20px;
                    width: 55px;
                    height: 55px;
                    font-size: 24px;
                }
            }
        </style>
        
        <!-- Chat Button -->
        <button class="chat-button" id="chatButton" onclick="chatbot.toggle()">
            <i class="fas fa-comment-dots"></i>
            <span class="badge">1</span>
        </button>
        
        <!-- Chat Window -->
        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <h3><i class="fas fa-robot"></i> CLEDUN FC AI Assistant</h3>
                <button class="chat-close" onclick="chatbot.toggle()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-suggestions">
                <button onclick="document.getElementById('chatInput').value='What teams do you have?'; chatbot.sendMessage();">🏆 Teams</button>
                <button onclick="document.getElementById('chatInput').value='What is your vision?'; chatbot.sendMessage();">👁️ Vision</button>
                <button onclick="document.getElementById('chatInput').value='Where are you located?'; chatbot.sendMessage();">📍 Location</button>
                <button onclick="document.getElementById('chatInput').value='How to buy tickets?'; chatbot.sendMessage();">🎟️ Tickets</button>
                <button onclick="document.getElementById('chatInput').value='Tell me about training'; chatbot.sendMessage();">🏋️ Training</button>
            </div>
            <div class="chat-footer">
                <input type="text" id="chatInput" placeholder="Ask me anything..." onkeypress="if(event.key==='Enter') chatbot.sendMessage();">
                <button onclick="chatbot.sendMessage();"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatHTML);
});