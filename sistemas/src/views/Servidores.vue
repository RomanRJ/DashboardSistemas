<template>
    <Sidebar />
    <div class="status-container">
        <iframe src="https://sistemas.lineaitalia.net/estatus/" frameborder="0"
            style="margin:0px 0px 0px 5vw; width: 95%; height: 95vh;"></iframe>
    </div>
</template>
<script setup>
import Sidebar from '../components/Sidebar.vue';
// import axios from 'axios';
// export default {
//     data() {
//         return {
//             status: [],
//             tipos: [],
//             loading: true,
//             modalVisible: false,
//             selectedServer: null
//         }
//     },
//     methods: {

//     },
//     created() {

//     }

// }

</script>
<style scoped>
.status-container {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}
</style>