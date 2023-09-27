<template>
    <div class="container">
        <b-row>
            <b-col size="12">
                <h1>Welcome to the Quizzed, write your nick below</h1>
            </b-col>
        </b-row>
        <b-row>
            <b-col size="12">
                <input type="text" class="form-control text-center" autofocus="autofocus" maxlength="12" id="nickkk" :value="nick" @input="onInput" />
            </b-col>
        </b-row>
        <b-row style="padding-top: 30px;">
            <b-col size="12" class="text-center">
                <input type="button" :class="classname" value="Submit" @click="setNick"/>
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
                classname: 'btn btn-primary',
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

                        location.href = 'user'; ///' + encodeURIComponent(response.data.nickFromServer);
                        // self.nick = response.data.nickFromServer ;
                        // self.id = response.data.id;
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
