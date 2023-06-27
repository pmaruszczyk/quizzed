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

        <question class="border border-dark" />
        <br>
        <b-button @click="showAnswer" variant="outline-danger">Reveal answer</b-button>
    </div>

</template>
<style>

</style>
<script>
    import axios from 'axios';

    import Question from './Question.vue';

    export default {
        components: {
            Question,
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
            }
        }

    }
</script>
