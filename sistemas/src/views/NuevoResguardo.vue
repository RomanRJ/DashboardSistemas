<script setup>
import Sidebar from '../components/Sidebar.vue';
import router from '@/router';

</script>
<template>
    <div class="main-container">
        <Sidebar />
        <div class="content">
            <div class="container"><br>
                <h2>Registrar Nuevo Resguardo</h2><br>
                <div class="container">
                    <div class="row">
                        <h3>Datos del empleado</h3>
                        <div class="col">
                            <label>Nombre:</label>
                            <input v-model="nombreEmpleado" class="form-control" type="text" required><br>
                            <label>Departamento:</label>
                            <input v-model="departamento" class="form-control" type="text" required><br>
                            <label>Tiempo que se le será prestado:</label>
                            <input v-model="tiempoPrestamo" class="form-control" type="text" required><br>
                        </div>
                        <div class="col">
                            <label>No. Nómina:</label>
                            <input v-model="noNomina" class="form-control" type="text" required><br>
                            <label>Motivo del préstamo:</label>
                            <input v-model="motivoPrestamo" class="form-control" type="text" required><br>
                            <label>Condiciones en las que se entrega:</label>
                            <input v-model="condicionesEntrega" class="form-control" type="text" required><br>
                        </div>
                    </div>
                    <div class="row">
                        <h3>Artículos</h3>
                        <div class="col">
                            <label>&nbsp;</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="material-symbols-outlined input-group-text">search</span>
                                </div>
                                <input type="text" class="form-control" v-model="searchTerm" placeholder="Buscar..."
                                    @keyup.enter="agregarPrimerArticulo">
                            </div>
                            <div class="table-container" id="tabla-articulos">
                                <div class="text-center mt-4" v-if="loading">
                                    <img src="https://lineaitalia.net/imagenes/carga1.gif" style="width:20vh;"><br>
                                    <h1></h1>
                                </div>
                                <table class="table table-striped" v-else>
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">No. Serie</th>
                                            <th scope="col">Descripción</th>
                                            <th scope="col">Marca</th>
                                            <th scope="col">Modelo</th>
                                            <th scope="col">Agregar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in filteredData" :key="index">
                                            <td>{{ item.nombre }}</td>
                                            <td>{{ item.no_serie }}</td>
                                            <td>{{ item.descripcion }}</td>
                                            <td>{{ item.marca }}</td>
                                            <td>{{ item.modelo }}</td>
                                            <td><button class="btn btn-primary btn-sm form-control"
                                                    @click="agregarArticulo(item)"><span
                                                        class="material-symbols-outlined">add</span></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-4">
                            <label>Artículos seleccionados</label>
                            <div class="table-container">
                                <table class="table table-striped">
                                    <thead></thead>
                                    <tbody>
                                        <tr v-for="(articulo, index) in articulosSeleccionados" :key="index">
                                            <td>{{ articulo.nombre }}</td>
                                            <td>{{ articulo.descripcion }}</td>
                                            <td>{{ articulo.no_serie }}</td>
                                            <td>
                                                <button class="btn btn-danger btn-sm form-control"
                                                    @click="eliminarArticulo(index)">
                                                    <span class="material-symbols-outlined">close</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
                    <button @click.prevent="registrarResguardo" class="btn btn-primary form-control">
                        Registrar Resguardo
                    </button>
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
            nombreEmpleado: '',
            departamento: '',
            tiempoPrestamo: '',
            noNomina: '',
            motivoPrestamo: '',
            condicionesEntrega: '',
            loading: true,
            searchTerm: '',
            articulosSeleccionados: [],
        };
    },
    methods: {
        agregarArticulo(articulo) {
            this.articulosSeleccionados.push(articulo);
        },
        eliminarArticulo(index) {
            this.articulosSeleccionados.splice(index, 1);
        },
        agregarPrimerArticulo() {
            if (this.filteredData.length > 0) {
                this.agregarArticulo(this.filteredData[0]);
                this.searchTerm = '';
            }
        },
        refreshData() {
            axios.get('https://sistemas.lineaitalia.net/api/inventario/disponible')
                .then(data => {
                    this.data = data.data;

                    this.loading = false;
                }).catch(error => {
                    if (error.response.status === 401) {
                        this.$swal({
                            title: 'Debes iniciar sesión para continuar',
                            text: '',
                            icon: 'error',
                        }).then(() => {
                            router.push('/');
                        });
                    } else {
                        this.$swal('Error al cargar los productos:' + error, '', 'error');
                    }
                });
        },
        registrarResguardo() {
            // Extraer solo los IDs de los artículos seleccionados
            const articulosIds = this.articulosSeleccionados.map(articulo => articulo.id);

            const resguardoData = {
                empleado: {
                    nombre: this.nombreEmpleado,
                    departamento: this.departamento,
                    tiempoPrestamo: this.tiempoPrestamo,
                    noNomina: this.noNomina,
                    motivoPrestamo: this.motivoPrestamo,
                    condicionesEntrega: this.condicionesEntrega,
                },
                articulos: articulosIds,
            };



            axios.post('https://sistemas.lineaitalia.net/api/resguardos', resguardoData, { responseType: 'arraybuffer' })
                .then(response => {

                    this.nombreEmpleado = '';
                    this.departamento = '';
                    this.tiempoPrestamo = '';
                    this.noNomina = '';
                    this.motivoPrestamo = '';
                    this.condicionesEntrega = '';
                    this.articulosSeleccionados = [];
                    const blob = new Blob([response.data], { type: 'application/pdf' });

                    // Puedes mostrar el PDF en una ventana o pestaña
                    const url = window.URL.createObjectURL(blob);
                    this.refreshData();
                    window.open(url, '_blank');
                })
                .catch(error => {
                    if (error.response.status === 401) {
                        this.$swal({
                            title: 'Debes iniciar sesión para continuar',
                            text: '',
                            icon: 'error',
                        }).then(() => {
                            router.push('/');
                        });
                    } else {
                        this.$swal('Error al registrar resguardo:' + error, '', 'error');
                    }
                });
        },
    },
    computed: {
        filteredData() {
            return this.data.filter(item =>
                item.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                item.no_serie.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                item.descripcion.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                item.marca.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                item.modelo.toLowerCase().includes(this.searchTerm.toLowerCase())
            );
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

        axios.get('https://sistemas.lineaitalia.net/api/inventario/disponible')
            .then(data => {
                this.data = data.data;

                this.loading = false;
            }).catch(error => {
                if (error.response.status === 401) {
                    this.$swal({
                        title: 'Debes iniciar sesión para continuar',
                        text: '',
                        icon: 'error',
                    }).then(() => {
                        router.push('/');
                    });
                } else {
                    this.$swal('Error al cargar los productos:' + error, '', 'error');
                }
            });
    },
};
</script>
  

<style scoped>
span {
    vertical-align: middle;
}

thead {
    position: sticky;
    top: 0;
}

#tabla-articulos {
    height: 300px;
    overflow-y: scroll;
}
</style>