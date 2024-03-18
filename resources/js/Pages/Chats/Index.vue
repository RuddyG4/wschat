<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { onBeforeMount, onMounted, ref, watch } from 'vue';

const page = usePage()

const props = defineProps({
    chat: Object,
    messages: Object
});

const messages = ref(props.messages)
const channel = ref(Echo.join('chat.' + props.chat.id))
// const channel = ref(Echo.channel('chat'))
const isTyping = ref(false)
const imTyping = ref(false)
const chatUser = ref(null)
const chatUserOnlineStatus = ref(false)

const form = useForm({
    message: ''
});

const scrollToBottom = async () => {
    await new Promise(resolve => setTimeout(resolve, 80))
    const element = document.getElementById('messages')
    if (element) {
        element.scrollTop = element.scrollHeight
    }
}

const submit = function () {
    if (form.message == '') return
    form.post(route('chat.message.send', props.chat.id))
    scrollToBottom()
    form.message = ''
};

onMounted(function () {
    scrollToBottom()
    channel.value.listen('MessageSent', (e) => {
        showNewMessage(e.message);
    }).listenForWhisper('typing', (e) => {
        isTyping.value = e.typing
        scrollToBottom()
    }).here((users) => {
        if (users.length > 1) {
            chatUserOnlineStatus.value = true
        }
    }).joining((user) => {
        if (!chatUserOnlineStatus.value && user.id === chatUser.value.id) {
            chatUserOnlineStatus.value = true
        }
    }).leaving((user) => {
        if (chatUserOnlineStatus.value && user.id === chatUser.value.id) {
            chatUserOnlineStatus.value = false
        }
    })
})

onBeforeMount(function () {
    setUser()
})

function showNewMessage(message) {
    messages.value.push(message)
    scrollToBottom()
}

function setUser() {
    let i = 0
    while (chatUser.value == null && i < props.chat.users.length) {
        if (props.chat.users[i].id !== page.props.auth.user.id) {
            chatUser.value = props.chat.users[i]
        }
        i++
    }
}

watch(
    () => form.message,
    (message) => {
        if (!imTyping.value && message !== '') {
            imTyping.value = true
        }
        if (imTyping.value && message === '') {
            imTyping.value = false
        }
    }
)

watch(imTyping, (newValue) => {
    channel.value.whisper('typing', {
        typing: newValue
    })
})
</script>

<template>
    <!-- 
        source: https://codepen.io/supah/pen/jqOBqp
        Follow Supah on
        Dribbble: https://dribbble.com/supahfunk
        Twitter: https://twitter.com/supahfunk
        Codepen: https://codepen.io/supah/
     -->

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat view</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 text-gray-900 relative h-[75vh]">
                        <div class="chat max-h-[70vh]">
                            <div class="chat-title">
                                <h1>{{ chatUser.name }}</h1>
                                <h2 v-if="chatUserOnlineStatus">
                                    <span class="inline-flex w-2 h-2 me-1 bg-green-500 rounded-full"></span>
                                    Online
                                </h2>
                                <h2 v-else>
                                    <span class="inline-flex w-2 h-2 me-1 bg-red-500 rounded-full"></span>
                                    Offline
                                </h2>
                                <figure class="avatar">
                                    <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/156381/profile/profile-80.jpg">
                                </figure>
                            </div>
                            <div id="messages" class="messages">
                                <div class="messages-content mCustomScrollbar _mCS_1">
                                    <div id="mCSB_1" class="mCustomScrollBox mCS-light mCSB_vertical mCSB_inside"
                                        style="max-height: none;" tabindex="0">
                                        <div id="mCSB_1_container" class="mCSB_container"
                                            style="position: relative; top: -7px; left: 0px;" dir="ltr">
                                            <div v-for="message in messages">
                                                <div v-if="message.user_id == $page.props.auth.user.id"
                                                    class="message message-personal new">{{ message.content }}</div>
                                                <div v-else class="message new">
                                                    <figure class="avatar">
                                                        <img
                                                            src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/156381/profile/profile-80.jpg">
                                                    </figure>{{ message.content }}
                                                    <div class="timestamp">16:39</div>
                                                </div>
                                            </div>
                                            <div v-if="isTyping" class="message loading new">
                                                <figure class="avatar">
                                                    <img
                                                        src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/156381/profile/profile-80.jpg" />
                                                </figure><span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="message-box">
                                <textarea @keyup.enter="submit" v-model="form.message" type="text" class="message-input"
                                    placeholder="Type message..." required autofocus></textarea>
                                <button type="button" class="message-submit" @click="submit">Send</button>
                            </div>
                        </div>
                        <div class="bg"></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/*-------------------- Mixins --------------------*/
/*-------------------- Body --------------------*/
*,
*::before,
*::after {
    box-sizing: border-box;
}

html,
body {
    height: 100%;
}

body {
    background: linear-gradient(135deg, #044f48, #2a7561);
    background-size: cover;
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
    line-height: 1.3;
}

.bg {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 1;
    background: url("https://images.unsplash.com/photo-1451186859696-371d9477be93?crop=entropy&fit=crop&fm=jpg&h=975&ixjsv=2.1.0&ixlib=rb-0.3.5&q=80&w=1925") no-repeat 0 0;
    filter: blur(80px);
    transform: scale(1.2);
}

/*-------------------- Chat --------------------*/
.chat {
    position: absolute;
    /*top: 50%;
    left: 50%; 
    transform: translate(-50%, -50%); */
    width: 300px;
    height: 80vh;
    z-index: 2;
    overflow: hidden;
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.2);
    background: rgba(0, 0, 0, 0.5);
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
    flex-direction: column;
}

/*-------------------- Chat Title --------------------*/
.chat-title {
    flex: 0 1 45px;
    position: relative;
    z-index: 2;
    background: rgba(0, 0, 0, 0.2);
    color: #fff;
    text-transform: uppercase;
    text-align: left;
    padding: 10px 10px 10px 50px;
}

.chat-title h1,
.chat-title h2 {
    font-weight: normal;
    font-size: 10px;
    margin: 0;
    padding: 0;
}

.chat-title h2 {
    color: rgba(255, 255, 255, 0.5);
    font-size: 8px;
    letter-spacing: 1px;
}

.chat-title .avatar {
    position: absolute;
    z-index: 1;
    top: 8px;
    left: 9px;
    border-radius: 30px;
    width: 30px;
    height: 30px;
    overflow: hidden;
    margin: 0;
    padding: 0;
    border: 2px solid rgba(255, 255, 255, 0.24);
}

.chat-title .avatar img {
    width: 100%;
    height: auto;
}

/*-------------------- Messages --------------------*/
.messages {
    flex: 1 1 auto;
    color: rgba(255, 255, 255, 0.5);
    overflow: auto;
    position: relative;
    width: 100%;
}

.messages .messages-content {
    position: absolute;
    top: 0;
    left: 0;
    height: 101%;
    width: 100%;
}

.messages .message {
    clear: both;
    float: left;
    padding: 6px 10px 7px;
    border-radius: 10px 10px 10px 0;
    background: rgba(0, 0, 0, 0.3);
    margin: 10px 0;
    font-size: 11px;
    line-height: 1.4;
    margin-left: 35px;
    position: relative;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
}

.messages .message .timestamp {
    position: absolute;
    bottom: -15px;
    font-size: 9px;
    color: rgba(255, 255, 255, 0.3);
}

.messages .message::before {
    content: "";
    position: absolute;
    bottom: -6px;
    border-top: 6px solid rgba(0, 0, 0, 0.3);
    left: 0;
    border-right: 7px solid transparent;
}

.messages .message .avatar {
    position: absolute;
    z-index: 1;
    bottom: -15px;
    left: -35px;
    border-radius: 30px;
    width: 30px;
    height: 30px;
    overflow: hidden;
    margin: 0;
    padding: 0;
    border: 2px solid rgba(255, 255, 255, 0.24);
}

.messages .message .avatar img {
    width: 100%;
    height: auto;
}

.messages .message.message-personal {
    float: right;
    color: #fff;
    text-align: right;
    background: linear-gradient(120deg, #248a52, #257287);
    border-radius: 10px 10px 0 10px;
}

.messages .message.message-personal::before {
    left: auto;
    right: 0;
    border-right: none;
    border-left: 5px solid transparent;
    border-top: 4px solid #257287;
    bottom: -4px;
}

.lastChild {
    margin-bottom: 30px;
}

.messages .message.new {
    transform: scale(0);
    transform-origin: 0 0;
    animation: bounce 500ms linear both;
}

.messages .message.loading::before {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    content: "";
    display: block;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    z-index: 2;
    margin-top: 4px;
    animation: ball 0.45s cubic-bezier(0, 0, 0.15, 1) alternate infinite;
    border: none;
    animation-delay: 0.15s;
}

.messages .message.loading span {
    display: block;
    font-size: 0;
    width: 20px;
    height: 10px;
    position: relative;
}

.messages .message.loading span::before {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    content: "";
    display: block;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    z-index: 2;
    margin-top: 4px;
    animation: ball 0.45s cubic-bezier(0, 0, 0.15, 1) alternate infinite;
    margin-left: -7px;
}

.messages .message.loading span::after {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    content: "";
    display: block;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    z-index: 2;
    margin-top: 4px;
    animation: ball 0.45s cubic-bezier(0, 0, 0.15, 1) alternate infinite;
    margin-left: 7px;
    animation-delay: 0.3s;
}

/*-------------------- Message Box --------------------*/
.message-box {
    flex: 0 1 40px;
    width: 100%;
    background: rgba(0, 0, 0, 0.3);
    padding: 10px;
    position: relative;
}

.message-box .message-input {
    background: none;
    border: none;
    outline: none !important;
    resize: none;
    color: rgba(255, 255, 255, 0.7);
    font-size: 11px;
    height: 17px;
    margin: 0;
    padding-right: 20px;
    width: 265px;
}

.message-box textarea:focus:-webkit-placeholder {
    color: transparent;
}

[type='text']:focus {
    box-shadow: none;
    --tw-ring-shadow: 0 0 #000 !important;
}

[type='text'] {
    padding-bottom: 0;
}

.message-box .message-submit {
    position: absolute;
    z-index: 1;
    top: 9px;
    right: 10px;
    color: #fff;
    border: none;
    background: #248a52;
    font-size: 10px;
    text-transform: uppercase;
    line-height: 1;
    padding: 6px 10px;
    border-radius: 10px;
    outline: none !important;
    transition: background 0.2s ease;
}

.message-box .message-submit:hover {
    background: #1d7745;
}

/*-------------------- Custom Srollbar --------------------*/
.mCSB_scrollTools {
    margin: 1px -3px 1px 0;
    opacity: 0;
}

.mCSB_inside>.mCSB_container {
    margin-right: 0px;
    padding: 0 10px;
}

.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar {
    background-color: rgba(0, 0, 0, 0.5) !important;
}

/*-------------------- Bounce --------------------*/
@keyframes bounce {
    0% {
        transform: matrix3d(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    4.7% {
        transform: matrix3d(0.45, 0, 0, 0, 0, 0.45, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    9.41% {
        transform: matrix3d(0.883, 0, 0, 0, 0, 0.883, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    14.11% {
        transform: matrix3d(1.141, 0, 0, 0, 0, 1.141, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    18.72% {
        transform: matrix3d(1.212, 0, 0, 0, 0, 1.212, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    24.32% {
        transform: matrix3d(1.151, 0, 0, 0, 0, 1.151, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    29.93% {
        transform: matrix3d(1.048, 0, 0, 0, 0, 1.048, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    35.54% {
        transform: matrix3d(0.979, 0, 0, 0, 0, 0.979, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    41.04% {
        transform: matrix3d(0.961, 0, 0, 0, 0, 0.961, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    52.15% {
        transform: matrix3d(0.991, 0, 0, 0, 0, 0.991, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    63.26% {
        transform: matrix3d(1.007, 0, 0, 0, 0, 1.007, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    85.49% {
        transform: matrix3d(0.999, 0, 0, 0, 0, 0.999, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }

    100% {
        transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);
    }
}

@keyframes ball {
    from {
        transform: translateY(0) scaleY(0.8);
    }

    to {
        transform: translateY(-10px);
    }
}

/* width */
::-webkit-scrollbar {
    width: 5px;
}

/* Track */
::-webkit-scrollbar-track {
    background: #888;
}

/* Handle */
::-webkit-scrollbar-thumb {
    /* background: #f1f1f1; */
    background: #333;
    border-radius: 10px;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
    background: #222;
}
</style>