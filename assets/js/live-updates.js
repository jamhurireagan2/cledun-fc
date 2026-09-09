// Live match auto-refresh
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh live matches every 30 seconds
    setInterval(function() {
        const liveSection = document.querySelector('.live-section');
        if (liveSection) {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newLive = doc.querySelector('.live-section');
                    if (newLive) {
                        liveSection.innerHTML = newLive.innerHTML;
                    }
                })
                .catch(() => {});
        }
    }, 30000);
});