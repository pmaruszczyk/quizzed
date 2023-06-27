<template>
    <div>
        <div class="container text-center">
            <div class="row">
                <div class="col-12">
                    <div :class="welcome_class">
                        <h1>{{ welcome_text }}</h1>
                    </div>
                </div>
            </div>
        </div>
        <div :class="question_class">
            <div class="container text-center">
                <b-row>
                    <b-col size="12">
                        <b-progress :value="progress_value" :max="progress_max" class="mb-3"></b-progress>
                    </b-col>
                </b-row>
                <div class="row">
                    <div class="col-12">
                        <div class="question-title"><h1>{{ title }}</h1></div>
                        <div class="image"><img :src="image_src"/></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 success">
<!--                        <h1>{{ new_points }}</h1>-->
                    </div>
                </div>
                <div class="row">
                    <div class="d-grid gap-1 col-6 mx-auto">
                        <b-button @click="chooseAnswerA" :variant="variantA">{{ answer1 }}</b-button>
                        <b-button @click="chooseAnswerB" :variant="variantB">{{ answer2 }}</b-button>
                        <b-button @click="chooseAnswerC" :variant="variantC">{{ answer3 }}</b-button>
                        <b-button @click="chooseAnswerD" :variant="variantD">{{ answer4 }}</b-button>
                    </div>
                </div>
                <div class="row">
                    <b-container class="text-right w-50 mt-1"  :class="stats_class">
                        <b-row>
                            <b-col class="w-25">{{ answer1 }}</b-col>
                            <b-col class="w-25"><b-progress :value="stats.A" :max="stats_maximum" :variant="variantA" show-value class="mb-3"></b-progress></b-col>
                        </b-row>
                        <b-row>
                            <b-col>{{ answer2 }}</b-col>
                            <b-col><b-progress :value="stats.B" :max="stats_maximum" :variant="variantB" show-value class="mb-3"></b-progress></b-col>
                        </b-row>
                        <b-row>
                            <b-col>{{ answer3 }}</b-col>
                            <b-col><b-progress :value="stats.C" :max="stats_maximum" :variant="variantC" show-value class="mb-3"></b-progress></b-col>
                        </b-row>
                        <b-row>
                            <b-col>{{ answer4 }}</b-col>
                            <b-col><b-progress :value="stats.D" :max="stats_maximum" :variant="variantD" show-value class="mb-3"></b-progress></b-col>
                        </b-row>
                    </b-container>

                </div>
            </div>
        </div>
    </div>
</template>
<style>
    .hidden {
        display: none;
    }
    h1 {
        text-align: center;
        margin: 30px auto;
    }
    button {
        width: 100%;
    }
    .image img {
        height:30vh;

    }
</style>
<script>
    import axios from 'axios';
    let loopId = null;
    export default {
        mounted() {
            loopId=setInterval(this.getQuestion, 1000);

        },
        data() {
            return {
                welcome_text: 'Welcome in the quiz!',
                welcome_class: '',
                question_class: 'hidden',
                title: '',
                answer1: '',
                answer2: '',
                answer3: '',
                answer4: '',
                stats_maximum: 0,
                stats_class: 'hidden',
                stats: [],
                question_id: 0,
                variantA: 'outline-dark',
                variantB: 'outline-dark',
                variantC: 'outline-dark',
                variantD: 'outline-dark',
                new_points: '',
                image_src: '',
                question_active: true,
                progress_value: 40,
                progress_max: 40,
            }
        },
        methods: {
            markAsValid(obj, property) {
                obj[property] = 'success';
            },
            markAsInvalid(obj, property) {
                if (obj[property] === 'warning' || obj[property] === 'danger') {
                    obj[property] = 'danger';
                } else {
                    obj[property] = '';
                }
            },
            populateQuestion(Q) {
                if (Q.id=='-1') {
                    clearInterval(loopId);
                    clearInterval(this.interval);
                    this.welcome_class = '';
                    this.question_class = 'hidden';
                    this.welcome_text= 'It was the last question.';
                    return;
                }

                if (Q.id == 0) {
                    return;
                }

                if (Q.correct) {
                    clearInterval(this.interval);

                    switch (Q.correct) {
                        case 'A':   this.markAsValid(this, 'variantA');
                                    this.markAsInvalid(this, 'variantB');
                                    this.markAsInvalid(this, 'variantC');
                                    this.markAsInvalid(this, 'variantD');
                        break;
                        case 'B':   this.markAsValid(this, 'variantB');
                                    this.markAsInvalid(this, 'variantA');
                                    this.markAsInvalid(this, 'variantC');
                                    this.markAsInvalid(this, 'variantD');
                        break;
                        case 'C':   this.markAsValid(this, 'variantC');
                                    this.markAsInvalid(this, 'variantA');
                                    this.markAsInvalid(this, 'variantB');
                                    this.markAsInvalid(this, 'variantD');
                        break;
                        case 'D':   this.markAsValid(this, 'variantD');
                                    this.markAsInvalid(this, 'variantA');
                                    this.markAsInvalid(this, 'variantB');
                                    this.markAsInvalid(this, 'variantC');
                        break;
                    }
                }

                if (Q.id == this.question_id) {
                    return;
                }
                clearInterval(this.interval);

                this.progress_max = Q.time_per_question;
                let i = Q.time_per_question;
                let self = this;
                this.interval = setInterval(function () {
                    i--;
                    if (i >= 0) {
                        self.progress_value=i;
                    } else {
                        clearInterval(self.interval);
                    }
                }, 1000);

                this.question_active = true;
                this.variantA = 'outline-dark';
                this.variantB = 'outline-dark';
                this.variantC = 'outline-dark';
                this.variantD = 'outline-dark';
                this.welcome_class = 'hidden';
                this.question_class = '';
                this.title = Q.question;
                this.answer1 = Q.answers.A;
                this.answer2 = Q.answers.B;
                this.answer3 = Q.answers.C;
                this.answer4 = Q.answers.D;
                this.image_src = '/img/' + Q.image;
                this.new_points ='';
                this.question_id = Q.id;
            },
            populateStats(stats) {
                if (Array.isArray(stats) && stats.length === 0) {
                    this.stats_class = 'hidden';
                    return;
                }

                this.stats_class = '';
                this.stats = stats;
                this.stats_maximum = Math.max(stats.A, stats.B, stats.C, stats.D);
            },
            getQuestion(question) {
                let self = this;
                axios.get('/question', {})
                    .then(function (response) {
                        const question = response.data.question;
                        const stats = response.data.stats;
                        self.populateQuestion(question);
                        self.populateStats(stats);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            sendAnswer(letter) {
                let self = this;
                if (!this.question_active) {
                    return;
                }

                this['variant' + letter] = 'warning';

                axios.post('/saveAnswer', {answer: letter})
                    .then(function (response) {
                        this.question_active = false;
                        self.new_points = '+' + response.data;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });

            },
            chooseAnswerA() { this.sendAnswer('A');},
            chooseAnswerB() { this.sendAnswer('B');},
            chooseAnswerC() { this.sendAnswer('C');},
            chooseAnswerD() { this.sendAnswer('D');},
        }
    }
</script>
