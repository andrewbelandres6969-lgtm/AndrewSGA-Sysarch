const TRANSITION_DURATION = 350;

function fadeOut(element, callback) {
    if (!element) return;
    element.classList.remove('fade-in');
    element.classList.add('fade-out');
    setTimeout(() => {
        element.classList.add('hidden');
        element.classList.remove('fade-out');
        if (typeof callback === 'function') callback();
    }, TRANSITION_DURATION);
}

function fadeIn(element) {
    if (!element) return;
    element.classList.remove('hidden');
    requestAnimationFrame(() => {
        element.classList.remove('fade-out');
        element.classList.add('fade-in');
    });
}

function showHome() {
    const home = document.getElementById("homeSection");
    const reg = document.getElementById("registerSection");
    const log = document.getElementById("loginSection");

    fadeOut(reg);
    fadeOut(log);
    fadeIn(home);
}

function showRegister() {
    const home = document.getElementById("homeSection");
    const reg = document.getElementById("registerSection");
    const log = document.getElementById("loginSection");

    fadeOut(home);
    fadeOut(log);
    fadeIn(reg);
}

function showLogin() {
    const home = document.getElementById("homeSection");
    const reg = document.getElementById("registerSection");
    const log = document.getElementById("loginSection");

    fadeOut(home);
    fadeOut(reg);
    fadeIn(log);
}

function pageTransition(url) {
    document.body.classList.add('page-fade-out');
    setTimeout(() => {
        window.location.href = url;
    }, TRANSITION_DURATION);
}

const countdownElement = document.getElementById("countdown");

if (countdownElement) {
    const endTime = new Date(countdownElement.dataset.end).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance <= 0) {
            countdownElement.innerHTML = "Session expired";
            return;
        }

        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.innerHTML = hours + "h " + minutes + "m " + seconds + "s";
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
}document.addEventListener('DOMContentLoaded', function () {
    const changeButton = document.getElementById('changePhotoButton');
    const photoControls = document.getElementById('photoUploadControls');
    const photoInput = document.getElementById('photoInput');

    if (changeButton && photoControls) {
        changeButton.addEventListener('click', function () {
            photoControls.classList.remove('hidden');
            changeButton.classList.add('hidden');
            if (photoInput) {
                photoInput.click();
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const messageBanner = document.querySelector('.message');
    const announcementBanner = document.querySelector('.announcement-banner');

    const hideBanner = (banner) => {
        banner.style.transition = 'opacity 0.35s ease';
        banner.style.opacity = '0';
        setTimeout(() => {
            if (banner.parentNode) {
                banner.parentNode.removeChild(banner);
            }
        }, 350);
    };

    if (messageBanner) {
        setTimeout(() => hideBanner(messageBanner), 10000);
    }

    if (announcementBanner) {
        setTimeout(() => hideBanner(announcementBanner), 10000);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    const closeModalButtons = document.querySelectorAll('[data-modal-close]');

    const toggleBodyScroll = () => {
        const hasOpenModal = document.querySelector('.modal-overlay:not(.hidden)');
        document.body.classList.toggle('modal-open', Boolean(hasOpenModal));
    };

    openModalButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const modal = document.getElementById(button.dataset.modalTarget);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            button.classList.add('active');
            toggleBodyScroll();
        });
    });

    closeModalButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const modal = document.getElementById(button.dataset.modalClose);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.querySelectorAll('[data-modal-target="' + button.dataset.modalClose + '"]').forEach((trigger) => {
                trigger.classList.remove('active');
            });
            toggleBodyScroll();
        });
    });

    document.querySelectorAll('.modal-overlay').forEach((modal) => {
        modal.addEventListener('click', function (event) {
            if (event.target !== modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.querySelectorAll('[data-modal-target="' + modal.id + '"]').forEach((trigger) => {
                trigger.classList.remove('active');
            });
            toggleBodyScroll();
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.modal-overlay:not(.hidden)').forEach((modal) => {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.querySelectorAll('[data-modal-target="' + modal.id + '"]').forEach((trigger) => {
                trigger.classList.remove('active');
            });
        });
        toggleBodyScroll();
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const announcementForm = document.getElementById('announcementForm');
    const announcementIdInput = document.getElementById('announcementId');
    const announcementActionInput = document.getElementById('announcementAction');
    const announcementText = document.getElementById('announcementText');
    const cancelEditButton = document.getElementById('announcementCancelEdit');
    const editButtons = document.querySelectorAll('.announcement-edit-btn');

    if (!announcementForm || !announcementIdInput || !announcementActionInput || !announcementText) {
        return;
    }

    const resetAnnouncementForm = () => {
        announcementIdInput.value = '';
        announcementActionInput.value = 'save';
        announcementText.value = '';
        announcementText.placeholder = 'Write an announcement for students...';
        if (cancelEditButton) {
            cancelEditButton.classList.add('hidden');
        }
    };

    editButtons.forEach((button) => {
        button.addEventListener('click', function () {
            announcementIdInput.value = button.dataset.announcementId || '';
            announcementActionInput.value = 'save';
            announcementText.value = button.dataset.announcementContent || '';
            announcementText.focus();
            announcementText.placeholder = 'Update the selected announcement...';
            if (cancelEditButton) {
                cancelEditButton.classList.remove('hidden');
            }
        });
    });

    if (cancelEditButton) {
        cancelEditButton.addEventListener('click', function () {
            resetAnnouncementForm();
        });
    }
});
