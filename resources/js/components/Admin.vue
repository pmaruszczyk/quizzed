<template>
    <div class="container">
        Hello, write your nick
        <input type="text" maxlength="10" id="nickkk" :value="nick" @input="onInput" />
        <input type="button" :class="classname" value="Submit" @click="setNick"/>
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
        // mounted() {
        //     alert('Component mounted.')
        // }
        data() {
            return {
                nick: '',
                classname: '',
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
                    if (response.data.nickFromServer) {
                        self.nick = response.data.nickFromServer;
                        self.id = response.data.id;
                        self.classname= 'hidden';
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            }
        }
    }
</script>
