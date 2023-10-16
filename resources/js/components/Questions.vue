<template>
    <div class="questions">
         <b-row>
            <b-col cols="9">
                <h3>Question: {{currentQuestion}} / {{questionCount}}</h3>
            </b-col>
            <b-col cols="3">
                <b-button @click="nextScreen" variant="outline-info">Next</b-button>
            </b-col>
        </b-row>
        <player-screen class="border border-dark" />

        <br>
        <br>
        <br>

        <b-row>
            <b-col cols="3">
                <b-button @click="resetGame" variant="outline-secondary" size="sm">Reset game</b-button>
            </b-col>
            <b-col cols="3">Deprecated: </b-col>
            <b-col cols="3">
                <b-button @click="showAnswer" variant="outline-danger" size="sm">Reveal answer</b-button>
            </b-col>
            <b-col cols="3">
                <b-button @click="nextQuestion" variant="outline-info" size="sm">Next question</b-button>
            </b-col>
        </b-row>
    </div>

</template>
<style>
    .questions .player-header {display: none;}
</style>
<script>
    import axios from 'axios';
    import PlayerScreen from './PlayerScreen.vue';

    // TODO value for this variable should be always taken from the backend.
    //  For now I start with 'revealed', so if admin will refresh page answer
    //  was not revealed then the reveal will be omitted.
    let currentState = 'revealed';

    export default {
        components: {
            PlayerScreen,
        },
        mounted() {
            let self = this;
            axios.get('/questions567')
                .then(function (response) {
                    if (response.data) {
                        self.questions = response.data;
                        self.questionCount = self.questions.length -1;
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        data() {
            return {
                questions: [],
                currentQuestion: 0,
                questionCount: 0,
            }
        },
        methods: {
            nextScreen() {
                switch(currentState) {
                    case 'revealed': this.nextQuestion(); break;
                    case 'not-revealed': this.showAnswer();  break;
                }
            },
            nextQuestion() {
                let self = this;
                axios.get('/goToNextStep')
                    .then(function (response) {
                        if (response.data) {
                            self.currentQuestion = Math.min(response.data, self.questionCount);
                            currentState = 'not-revealed';
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            showAnswer() {
                axios.get('/showAnswer').then(response => { currentState = 'revealed'; });
            },
            resetGame() {
                if (window.confirm('Users will be removed. Quiz will be reset. Do you want to do it?')) {
                    axios.post('/resetGame').then(response => location.href = location.href);
                }
            }
        }

    }
</script>
