<script setup>
import Sidebar from '../components/Sidebar.vue';
</script>

<template>
    <div class="main-container">
        <Sidebar />
        <div class="content">
            <div class="container"><br>
                <h2>Bitacora</h2><br>
                <div class="container">
                    <div class="row">
                        <div class="col-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalNuevoReporte"
                                @click="obetenerFecha"><span
                                    class="material-symbols-outlined">emoji_objects</span>Añadir nuevo reporte</button>
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
                                        <th scope="col">ID</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Responsable</th>
                                        <th scope="col">Descripción del reporte</th>
                                        <th scope="col">Descripción de las acciones para corregir</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><button class="btn btn-primary">Ver más</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalNuevoReporte" tabindex="-1" aria-labelledby="ModalNuevoReporteLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ModalNuevoReporteLabel">Nuevo Reporte</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <label>Fecha</label>
                                <input type="datetime" class="form-control" v-model="fecha" disabled>
                            </div>
                            <div class="col">
                                <label>Solicitante</label>
                                <input type="text" class="form-control" v-model="usuario" disabled>
                            </div>
                            <div class="row">
                                <label>Categoria</label>
                                <select class="form-select" v-model="categoria">
                                    <option>Aviso</option>
                                    <option>Revisión</option>
                                    <option>Mantenimiento</option>
                                    <option>Problema</option>
                                </select>
                                <label>Descripción detallada del problema</label>
                                <textarea class="form-control" rows="4" v-model="descripcion"></textarea>
                                <label>Descripción de acciónes para corregir</label>
                                <textarea class="form-control" rows="4" v-model="acciones"></textarea>
                                <label>Evidencia del problema</label>
                                <input type="file" class="form-control">
                                {{ hola }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" @click="EnviarDatos">Enviar</button>
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
            fecha: '',
            usuario: '',
            categoria: '',
            descripcion: '',
            acciones: '',
            evidencia: [],

            historial: [],
        };
    },
    methods: {
        obetenerFecha() {
            const date = new Date();
            this.fecha = ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" +
                date.getFullYear() + " " + ("0" + date.getHours()).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2);
        },
        EnviarDatos() {
        },
    },
    created() {
        try {
            const token = sessionStorage.getItem("token");
            if (token) {
                axios.defaults.headers.common['Authorization'] = token;
            }
        } catch (error) {
            router.push('/');
        }
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

textarea {
    resize: none;
}
</style>