document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
        const target = this.getAttribute('href');
        document.querySelector(target).style.display = 'block';
        this.classList.add('nav-tab-active');
    });
});

function confirmSubmit() {
    return confirm('Are you sure you want to save these prices?');
}
