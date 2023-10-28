<template>
    <div>
        <div class="container styled text-center">
            <div class="row">
                <div class="col-12">
                    <div :class="welcome_class">
                        <h1>{{ welcome_text }}</h1>
                        <div class="lds-dual-ring"/>
                    </div>
                </div>
            </div>
        </div>
        <div :class="question_class">
            <b-row class="player-header">
                <b-col size="6">
                    {{ nick }}
                </b-col>
                <b-col size="6">
                    <!-- TODO {{ points }} points,--> last gained: {{ new_points }}
                </b-col>
            </b-row>
            <div class="container text-center">
                <b-row>
                    <b-col size="12">
                        <b-progress :value="progress_value" :max="progress_max" class="mb-3"></b-progress>
                    </b-col>
                </b-row>
                <div class="row">
                    <div class="col-12">
                        <div class="question-title"><h1>{{ title }}</h1></div>
                        <div class="image">
                            <img :class="class_image" :src="image_src" @click="pointOnImage" />
                            <div class="mark mark-user hidden"></div>
                            <div class="mark mark-correct hidden"></div>
                            <div class="mark mark-other-player hidden"></div>
                        </div>
                    </div>
                </div>
                <div class="row" :class="class_answers_abcd">
                    <div class="d-grid gap-1 col-6 mx-auto">
                        <b-button v-for="(answer, index) in answers" @click="chooseAbcdAnswer(index)" :variant="answer.variant">{{ answer.value }}</b-button>
                    </div>
                </div>
                <div class="row">
                    <b-container class="text-right w-50 mt-1"  :class="stats_class">
                        <b-row v-for="(value, index) in stats">
                            <b-col class="w-25">{{ answers[index]['value'] }}</b-col>
                            <b-col class="w-25"><b-progress :value="value" :max="stats_maximum" :variant="answers[index]['variant']" show-value class="mb-3"></b-progress></b-col>
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
    .image {
        position: relative;
        display: inline-block;
    }
    .image img {
        height: 30vh;
    }
    .image .mark {
        background: none;
        border-radius: 50%;
        position: absolute;
        display: none;
    }
    .point .image img {
        cursor: pointer;
        height: 50vh;
    }
    .point .image>.mark {
        display: block;
    }
    .point .image .mark-user {
        border: 2px solid orange;
    }
    .point .image .mark-correct {
        border: 2px solid green;
    }
    .point .image .mark-other-player {
        border: 2px solid #ffb700;
        background: #ffb700;
    }
    .point .image>.mark.mark-other-player.hidden {
        display: none;
    }
    .player-header {
        background: #101042;
        color: white;
        width: 100%;
        margin: 0;
        text-align: left;
        height: 27px;
    }
</style>
<script>
    import axios from 'axios';
    let loopId = null;
    export default {
        emits: ['inform-about-state'],
        mounted() {
            loopId = setInterval(this.getQuestion, 1000);
        },
        data() {
            return {
                welcome_text: 'Welcome to the quiz! Waiting for players.',
                welcome_class: '',
                question_class: 'hidden',
                class_answers_abcd: 'hidden',
                waiting_for_answer: false,
                type: '',
                title: '',
                nick: '',
                points: 0,
                new_points: '',
                new_points_temporary_store: '',
                answers: {},
                stats_maximum: 0,
                stats_class: 'hidden',
                stats: [],
                question_id: 0,
                image_src: '',
                question_active: true,
                progress_value: 60, //TODO connect with backend?
                progress_max: 60, //TODO connect with backend?
            }
        },
        methods: {
            populateQuestion(question, playersPoints) {
                if (question.id == '-1') { //TODO  magic value - not descriptive
                    clearInterval(loopId); //TODO function called -> stop asking server
                    clearInterval(this.interval);//TODO function called ->  stop progressbar
                    this.welcome_class = '';
                    this.question_class = 'hidden';
                    this.welcome_text= 'It was the last question.';
                    return;
                }

                if (question.id == 0) {
                    return;
                }

                switch (question.type) {
                    case 'abcd': this.populateAbcdQuestion(question); break;
                    case 'point': this.populatePointQuestion(question, playersPoints); break;
                }

                if (question.revealed) {
                    this.showGainedPoints();
                    clearInterval(this.interval);
                    this.$emit('inform-about-state', 'revealed');
                } else {
                    this.$emit('inform-about-state', 'not-revealed');
                }

                if (question.id == this.question_id) {
                    return;
                }
                clearInterval(this.interval);

                // TODO -> Abstract ^ \/
                this.progress_max = question.time_per_question;
                let i = question.time_per_question;
                let self = this;
                this.interval = setInterval(function () {
                    i--;
                    if (i >= 0) {
                        self.progress_value = i;
                    } else {
                        clearInterval(self.interval);
                    }
                }, 1000);


                //TODO \/ this.initQuestion; General function
                this.question_active = true;
                this.welcome_class = 'hidden';
                this.question_class = question.type;
                this.waiting_for_answer = true;
                this.type = question.type;
                this.title = question.question;
                this.image_src = '/img/' + question.image;
                this.new_points = '';
                this.new_points_temporary_store = '';
                this.question_id = question.id;
                this.nick = question.nick;
                this.clearPointQuestionElements();

                switch (question.type) {
                    case 'abcd': this.initAbcdQuestion(question); break;
                    case 'point': this.initPointQuestion(question); break;
                }
            },
            initAbcdQuestion(question) {
                const self = this;
                const answers = new Map(Object.entries(question.answers));
                self.answers = {};
                answers.forEach((value, index) => {
                    self.answers[index] = {
                        value: value,
                        variant: 'outline-light',
                    };
                });
                this.stats = [];
                this.class_answers_abcd = '';
            },
            initPointQuestion(question) {
                this.class_answers_abcd = 'hidden';
                this.class_image = 'clickable';
                this.putMarkOnImage(-9e9, -9e9, 'mark-user');
                this.putMarkOnImage(-9e9, -9e9, 'mark-correct');
            },
            populateAbcdQuestion(question) {
                if (question.correct) {
                    const self = this;
                    const answers = new Map(Object.entries(question.answers));
                    answers.forEach((value, index) => {
                        if (!self.answers[index]) {
                            return;
                        }
                        self.answers[index]['variant'] = self.getAbcdInvalidVariant(self.answers[index]['variant']);
                    });

                    if (this.answers[question.correct]) {
                        this.answers[question.correct]['variant'] = this.getAbcdValidVariant();
                    }
                }
            },
            populatePointQuestion(question, playersPoints) {
                const imageLoaded = document.querySelector('.image img').width > 0;
                if (question.correct_width && imageLoaded) {
                    const left = this.convertToImageWidth(question.correct_width, question);
                    const top = this.convertToImageHeight(question.correct_height, question);

                    this.putMarkOnImage(left, top, 'mark-correct');

                    if (document.querySelectorAll('.mark-other-player:not(.hidden)').length != playersPoints.length) {
                        playersPoints.forEach(point => this.putOtherPlayerMark(point,question));
                    }
                }
            },
            populateStats(stats) {
                if (Array.isArray(stats) && stats.length === 0) {
                    this.stats_class = 'hidden';
                    return;
                }

                this.stats_class = '';
                this.stats = stats;
                const statsMap = new Map(Object.entries(stats));
                let values = [];
                statsMap.forEach((value, index) => {
                    values.push(value);
                });

                this.stats_maximum = Math.max(...values);
            },
            getQuestion(question) {
                let self = this;
                axios.get('/question', {})
                    .then(function (response) {
                        const question = response.data.question;
                        const stats = response.data.stats;
                        const playersPoints = response.data.playersPoints;
                        self.populateQuestion(question, playersPoints);
                        self.populateStats(stats);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            chooseAbcdAnswer(letter) {
                if (!this.waiting_for_answer) {
                    return;
                }
                this.waiting_for_answer = false;

                if (this.question_active) {
                    this.answers[letter]['variant'] = 'warning';
                }

                const answer = {
                    type: 'abcd',
                    letter: letter,
                }
                this.sendAnswer(answer);
            },
            pointOnImage(event) {
                if (this.type !== 'point') {
                    return;
                }

                if (!this.waiting_for_answer) {
                    return;
                }
                this.waiting_for_answer = false;

                const imageWidth = event.target.width;
                const imageHeight = event.target.height;

                const positionLeftInImage = event.clientX;
                const positionTopInImage = event.clientY;
                const rect = event.target.getBoundingClientRect();

                const positionXOfClick = positionLeftInImage - rect.left;
                const positionYOfClick = positionTopInImage - rect.top;
                const answer = {
                    'type': 'point',
                    'image-width': parseInt(imageWidth),
                    'image-height': parseInt(imageHeight),
                    'click-x': parseInt(positionXOfClick),
                    'click-y': parseInt(positionYOfClick),
                }

                this.putMarkOnImage(positionXOfClick, positionYOfClick, 'mark-user');
                this.sendAnswer(answer);
            },
            sendAnswer(answer) {
                let self = this;
                if (!this.question_active) {
                    return;
                }

                axios.post('/saveAnswer', answer)
                    .then(function (response) {
                        self.question_active = false;
                        self.new_points_temporary_store = response.data;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            putMarkOnImage(left, top, type) {
                const mark = document.querySelector(`.image .${type}`);
                const width = 40; //parseInt(mark.parentNode.height) * 0.1;
                const height = width;
                mark.className = mark.className.replace('hidden', '');
                mark.style.top = `${top - height/2}px`;
                mark.style.left =`${left - width/2}px`;
                mark.style.width = `${width}px`;
                mark.style.height = `${height}px`;
            },
            putOtherPlayerMark(point, question) {
                const templateMark = document.querySelector('.image .mark-other-player.hidden');
                const mark = templateMark.cloneNode();
                const left = this.convertToImageWidth(point[0], question);
                const top = this.convertToImageWidth(point[1], question);
                const width = 4;
                const height = width;
                mark.className = 'mark mark-other-player';
                mark.style.top = `${top - height/2}px`;
                mark.style.left =`${left - width/2}px`;
                mark.style.width = `${width}px`;
                mark.style.height = `${height}px`;
                templateMark.after(mark);
            },
            clearPointQuestionElements() {
                const marks = document.querySelectorAll('.image .mark-other-player:not(.hidden)');
                marks.forEach(mark => mark.remove());
            },
            getAbcdValidVariant() {
                return 'success';
            },
            getAbcdInvalidVariant(currentVariant) {
                if (currentVariant === 'warning' || currentVariant === 'danger') {
                    return 'danger';
                } else {
                    return '';
                }
            },
            showGainedPoints() {
                this.new_points = '+' + (this.new_points_temporary_store || 0);
            },
            convertToImageWidth(x, question) {
                const image = document.querySelector('.image img');
                return (x * image.width) / question.image_width;
            },
            convertToImageHeight(y, question) {
                const image = document.querySelector('.image img');
                return (y * image.height) / question.image_height;
            },
        }
    }
</script>
