<template>
    <div class="container styled">
        <b-row>
            <b-col size="12" class="text-center">
                <img class="logo" src="img/logo.png" alt="Quizzed">
                <h1>Write your nick below</h1>
            </b-col>
        </b-row>
        <b-row>
            <b-col size="12" class="text-center">
                <input id="nick" type="text" class="text-center" autofocus="autofocus" maxlength="12" :value="nick" @input="onInput" />
            </b-col>
        </b-row>
        <b-row class="text-center">
            <b-col size="12" class="text-center">
                <input type="button" :class="classname" value="Join the quiz" @click="setNick"/>
            </b-col>
        </b-row>
    </div>
</template>
<style>
    input {background: gray;}

    .hidden {
        display: none;
    }
</style>
<script>
    import axios from 'axios';
    export default {
        data() {
            return {
                nick: '',
                classname: 'join',
                id: ''
            }
        },
        methods: {
            onInput(e) {
                // a v-on handler receives the native DOM event
                // as the argument.
                this.nick = e.target.value
            },
            setNick() {

                let self = this;
                axios.post('/setNick', {
                    nick: this.nick
                })
                .then(function (response) {
                    if (response.data) {
                        location.href = 'user';
                        self.classname = 'hidden';
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            }
        }
    }
</script>
