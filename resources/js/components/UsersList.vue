<template>
    <div class="container">
        <b-row>
            <b-col size="6">
                <questions />
            </b-col>
            <b-col size="6">
            <h3>Number of players: {{ number_of_players }}</h3>
            <table class="table">
                <tr>
                    <th>Nick</th>
                    <th>Points</th>
                </tr>
                <tbody id="tbody1"></tbody>
            </table>
            </b-col>
        </b-row>
    </div>

</template>
<style>
</style>
<script>
    import axios from 'axios';
    import Questions from './Questions.vue';

    function writeTableRow(user) {
        const x = document.createElement('tr');
        if (user.answered) {
            x.className = 'table-success';
        }

        let splitted = user.nick.split('_');
        const number = splitted.slice(-1);
        const nick = splitted.slice(0, -1).join('_');

        x.innerHTML = '<td>' + nick + ' (unique: ' + number + ')</td><td>' + user.points + '</td>';
        return x;
    }

    function writeTable(users) {
        const x = document.getElementById('tbody1');
        x.innerHTML='';
        users.forEach((value) => {
            x.appendChild(writeTableRow(value));
        })
    }

    export default {
        data() {
            return {
                // users_results: [],
                number_of_players: 0
            }
        },
        components: {
            Questions
        },
        mounted() {
            const self = this;
            function x() {
                axios.get('/users', {
                    nick: this.nick
                })
                .then(function (response) {
                    writeTable(response.data);
                    self.users_results = response.data;
                    self.number_of_players = self.users_results.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
            }

            setInterval(x, 2000)
        },
    }
</script>
