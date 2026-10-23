importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyDa1vMHazFYsD9TGvtrkXoMfWjnXS35ofw",
    authDomain: "blue-wish.firebaseapp.com",
    projectId: "seahum-62bab",
    storageBucket: "seahum-62bab.firebasestorage.app",
    messagingSenderId: "719381631762",
    appId: "1:719381631762:web:d922cb34422885dd2f99cb",
    measurementId: "719381631762"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});