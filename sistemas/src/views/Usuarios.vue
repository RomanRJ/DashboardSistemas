<script setup>
import Sidebar from '../components/Sidebar.vue';
</script>

<template>
    <div class="main-container">
        <Sidebar />
        <div class="content">
            <div class="container"><br>
                <h2>Administración de Usuarios</h2><br>
                <div class="container">
                    <div class="row">
                        <div class="col-2">
                            <button class="btn btn-primary"><span
                                    class="material-symbols-outlined">person_add</span>Añadir nuevo</button>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-8">
                            <label>&nbsp;</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="material-symbols-outlined input-group-text">search</span>
                                </div>
                                <input type="text" class="form-control" v-model="searchTerm" placeholder="Buscar...">
                            </div>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="table-container">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">Usuario</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Correo</th>
                                        <th scope="col">Departamento</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>programacion</td>
                                        <td>Roman De Leon</td>
                                        <td>programacion@lineaitalia.com.mx</td>
                                        <td>Sistemas</td>
                                        <td><button class="btn btn-primary">Permisos</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    data() {
        return {
        };
    },
    methods: {
    },
    created() {
    },
    computed: {
    },
};
</script>
<style>
span {
    vertical-align: middle;
    margin-right: 10px;
}
</style>