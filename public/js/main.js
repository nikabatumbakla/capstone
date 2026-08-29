// ===== ROBIN ROSE TRADING - MAIN JS =====

// Navbar scroll effect
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 60) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
});

// Mobile nav toggle
const navToggle = document.getElementById('navToggle');
const navLinks = document.querySelector('.nav-links');
if (navToggle) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        navToggle.innerHTML = navLinks.classList.contains('open') ?
            '<i class="fa fa-times"></i>' :
            '<i class="fa fa-bars"></i>';
    });
}

// Chatbot logic
const chatToggle = document.getElementById('chatToggle');
const chatWindow = document.getElementById('chatWindow');
const chatClose = document.getElementById('chatClose');
const chatInput = document.getElementById('chatInput');
const chatBody = document.getElementById('chatBody');
const chatbotAvatar = chatToggle ? chatToggle.querySelector('img').src : '';

const responses = {
    'products & pricing': "We offer a wide range of medical supplies! Browse our full catalog at the Products page, or tell me a specific category like diagnostic, PPE, wound care, etc. 😊",
    'send a quote': "To request a quote, visit our Contact page or fill out our inquiry form. You can also call us at 09292379053 or email Redrosalinda1876@gmail.com 📋",
    'delivery info': "We deliver to institutional clients including schools, barangays, hospitals, and LGUs. We also offer In-Store Shopping and In-Store Pickup. Contact us for delivery schedules! 🚚",
    'contact us': "📞 Phone: 09292379053\n📧 Email: Redrosalinda1876@gmail.com\n📍 Address: Ortega St., Philippines\nWe're happy to help!",
    'irent service': "🏥 iRent is our medical equipment rental service! Great for temporary healthcare needs. Contact us for available equipment and pricing.",
    'iscan service': "📱 iScan allows walk-in customers to scan product barcodes for instant product lookup! Available in-store. Just bring your device or use our in-store scanner.",
    'hello': "Hello! Welcome to Robin Rose Trading! How can I assist you today? 😊",
    'hi': "Hi there! 👋 How can we help you today?",
    'hours': "Our store is open Monday-Saturday, 8:00 AM - 6:00 PM. Feel free to drop by Ortega St. anytime during business hours!",
    'fda': "Yes! Robin Rose Trading is FDA certified and BIR compliant. We ensure all products meet the highest quality and safety standards. ✅",
    default: "Thank you for your message! For specific inquiries, please call us at 09292379053 or email Redrosalinda1876@gmail.com. Our team will respond within 24 hours! 💙"
};

function addMessage(text, sender) {
    const wrap = document.createElement('div');
    wrap.classList.add('chat-message', sender);
    const html = sender === 'bot' ?
        `<img src="${chatbotAvatar}" class="msg-avatar" alt="bot"><div class="msg-bubble">${text.replace(/\n/g,'<br>')}</div>` :
        `<div class="msg-bubble">${text}</div>`;
    wrap.innerHTML = html;
    chatBody.appendChild(wrap);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function getBotReply(msg) {
    const lower = msg.toLowerCase().trim();
    for (const key in responses) {
        if (lower.includes(key)) return responses[key];
    }
    return responses.default;
}

function sendMessage() {
    const msg = chatInput.value.trim();
    if (!msg) return;
    addMessage(msg, 'user');
    chatInput.value = '';
    setTimeout(() => addMessage(getBotReply(msg), 'bot'), 600);
}

function sendQuick(text) {
    addMessage(text, 'user');
    setTimeout(() => addMessage(getBotReply(text), 'bot'), 600);
}

if (chatToggle) {
    chatToggle.addEventListener('click', () => {
        chatWindow.classList.toggle('open');
    });
}
if (chatClose) {
    chatClose.addEventListener('click', () => chatWindow.classList.remove('open'));
}
if (chatInput) {
    chatInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });
}

// Product filter
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const cat = btn.dataset.cat;
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
        });
    });
});

// Product search
const searchInput = document.getElementById('productSearch');
if (searchInput) {
    searchInput.addEventListener('input', () => {
        const val = searchInput.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.querySelector('.product-name') ? .textContent.toLowerCase() || '';
            card.style.display = name.includes(val) ? '' : 'none';
        });
    });
}

// Portal tabs
document.querySelectorAll('.portal-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.portal-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab) ? .classList.add('active');
    });
});

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.cat-card, .product-card, .why-card, .service-card, .team-card, .announcement-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});