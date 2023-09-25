<template>
    <div class="questions">
        <h3>Current question</h3>

        <b-pagination
            v-model="currentQuestion"
            :total-rows="questionCount"
            per-page="1"
            hide-goto-end-buttons="0"
            hide-ellipsis="0"
            limit="100"
            prev-text=""
            next-text=""

        ></b-pagination>

        <b-button @click="nextQuestion" variant="outline-info">Next question</b-button>

        <player-screen class="border border-dark" />
        <br>
        <b-button @click="showAnswer" variant="outline-danger">Reveal answer</b-button>
        <br><br>
        <b-row>
            <b-col cols="3">
                <b-button @click="resetGame" variant="outline-secondary" size="sm">Reset game</b-button>
            </b-col>
            <b-col cols="9"/>
        </b-row>
    </div>

</template>
<style>
    .questions .player-header {display: none;}
</style>
<script>
    import axios from 'axios';

    import PlayerScreen from './PlayerScreen.vue';

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
            nextQuestion() {
                let self = this;
                axios.get('/goToNextStep')
                    .then(function (response) {
                        if (response.data) {
                            self.currentQuestion = response.data;
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            showAnswer() {
                axios.get('/showAnswer');
            },
            resetGame() {
                //TODO Prompt
                axios.get('/resetGame'); //TODO endpoint
            }
        }

    }
</script>
