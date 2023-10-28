<template>
    <div class="container">
        <b-row>
            <b-col size="6">
                <admin-screen />
            </b-col>
            <b-col size="6">
            <h3>{{ number_of_players }} players</h3>
            <table class="table">
                <tbody id="tbody1">
                    <tr>
                        <th>Nick</th>
                        <th>Points</th>
                    </tr>
                    <tr v-for="user in users" :class="user.answered ? 'table-success' : 'table-light'">
                        <td v-b-tooltip.hover :title="user.unique">{{user.nick}}</td>
                        <td>{{user.points}}</td>
                    </tr>
                </tbody>
            </table>
            </b-col>
        </b-row>
    </div>

</template>
<style>
</style>
<script>
    import axios from 'axios';
    import AdminScreen from './AdminScreen.vue';

    function preprocess(user) {
        const split = user.nick.split('_');
        const number = split.slice(-1).join('');
        const nick = split.slice(0, -1).join('_');

        return {
            nick: nick,
            unique: number,
            points: user.points,
            answered: user.answered,
        }
    }

    function preprocessUsers(users) {
        users.forEach((user, index) => {
            users[index] = preprocess(user);
        });
        return users;
    }

    export default {
        data() {
            return {
                users: [],
                number_of_players: 0,
            }
        },
        components: {
            AdminScreen
        },
        mounted() {
            const self = this;
            function refreshUsersList() {
                axios.get('/users', {
                    nick: this.nick
                })
                .then(function (response) {
                    self.users = preprocessUsers(response.data);
                    self.number_of_players = self.users.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
            }

            setInterval(refreshUsersList, 2000)
        },
    }
</script>
