const csrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

const vapidKey = () => {
    const base64 = document.querySelector('meta[name="vapid-public-key"]').content;
    const padded = (base64 + '='.repeat((4 - (base64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');

    return Uint8Array.from(atob(padded), (char) => char.charCodeAt(0));
};

const isIos = () => /iphone|ipad|ipod/i.test(navigator.userAgent);
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

export function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
}

/**
 * Alpine component behind the "Push aktivieren" switch on the settings page.
 */
export function pushToggle() {
    return {
        state: 'loading', // loading | unsupported | needs-install | denied | off | on
        busy: false,

        async init() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                this.state = isIos() && !isStandalone() ? 'needs-install' : 'unsupported';
                return;
            }

            if (Notification.permission === 'denied') {
                this.state = 'denied';
                return;
            }

            const registration = await navigator.serviceWorker.ready;
            this.state = (await registration.pushManager.getSubscription()) ? 'on' : 'off';
        },

        async enable() {
            this.busy = true;

            try {
                if ((await Notification.requestPermission()) !== 'granted') {
                    this.state = 'denied';
                    return;
                }

                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: vapidKey(),
                });

                await this.send('POST', {
                    ...subscription.toJSON(),
                    contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
                });

                this.state = 'on';
            } finally {
                this.busy = false;
            }
        },

        async disable() {
            this.busy = true;

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    await this.send('DELETE', { endpoint: subscription.endpoint });
                    await subscription.unsubscribe();
                }

                this.state = 'off';
            } finally {
                this.busy = false;
            }
        },

        send(method, body) {
            return fetch('/push-subscriptions', {
                method,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(body),
            });
        },
    };
}
