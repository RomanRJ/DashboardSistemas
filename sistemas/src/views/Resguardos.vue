<script setup>
import router from '@/router';
import Sidebar from '../components/Sidebar.vue';
</script>
<template>
    <div class="main-container">
        <Sidebar />
        <div class="content">
            <div class="container"><br>
                <h2>Listado De Resguardos</h2><br>
                <div class="container">
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
                        <div class="col-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" v-model="selectedState" @change="filterByState"
                                aria-label="Default select example">
                                <option value="" selected>Todos</option>
                                <option value="1">Activo</option>
                                <option value="0">Devolución Completa</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4" v-if="loading" style="background-color;">
                    <img src="https://lineaitalia.net/imagenes/carga1.gif" style="width:30vh;"><br>
                    <h1></h1>
                </div>
                <div class="table-container" v-else>
                    <table class="table table-striped">
                        <thead class="table-dark" id="principal">
                            <tr>
                                <th scope="col">Estado</th>
                                <th scope="col">Folio</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">No. Nómina</th>
                                <th scope="col">Departamento</th>
                                <th scope="col">Fecha de entrega</th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(datos, index) in filteredData" :key="index">
                                <td style="text-align: center;">
                                    <span class="activo" v-if="datos.estado == 2" alt="activo">&nbsp;</span>
                                    <span class="inactivo" v-else-if="datos.estado == 0" alt="inactivo">&nbsp;</span>
                                </td>
                                <td>{{ datos.resguardo.id }}</td>
                                <td>{{ datos.resguardo.usuario }}</td>
                                <td>{{ datos.resguardo.nomina }}</td>
                                <td>{{ datos.resguardo.departamento }}</td>
                                <td>{{ datos.resguardo.fecha_entrega }}</td>
                                <td>
                                    <button class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#exampleModal"
                                        @click="vistaDetalles(datos.resguardo.usuario, datos.articulos, datos.resguardo.id)">Ver
                                        Artículos</button>
                                </td>
                                <td>
                                    <button class="btn btn-secondary" @click="Reimpresion(datos.resguardo)">Reimprimir
                                        Ficha</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="..." aria-hidden="true"
        v-if="selectedItem !== null" @hidden.bs.modal="resetCheckboxes">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Artículos en resguardo de {{ nombre_resguardo }}
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-container">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">No. Serie</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" v-if="hayElementosSeleccionados">Condiciones de entrega</th>
                                    <th scope="col" v-if="hayElementosSeleccionados">¿Requiere reparación?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(datos, index) in selectedItem" :key="index">
                                    <td v-if="!datos.devuelto">
                                        <input type="checkbox" class="form-check-input dev-check" id=""
                                            @change="toggleSeleccion(index)">
                                    </td>
                                    <td v-else></td>
                                    <td>{{ datos.nombre }}</td>
                                    <td>{{ datos.descripcion }}</td>
                                    <td>{{ datos.no_serie }}</td>
                                    <td v-if="!datos.devuelto">Activo</td>
                                    <td v-else>Devuelto el {{ datos.fecha_devolucion }}</td>
                                    <td v-if="hayElementosSeleccionados"><input type="text" class="form-control"
                                            v-model="detallesPorId[index]" placeholder="¿Tiene algún detalle?"
                                            @change="handleConditionsChange(datos.id, index)">
                                    </td>
                                    <td v-if="hayElementosSeleccionados">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                :id="'flexSwitchCheckDefault_' + index"
                                                @change="markForRepair(datos.id, index)">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" v-if="hayElementosSeleccionados"
                        @click="Devolucion">Devolver
                        Artículos</button>
                    <button type="button" class="btn btn-secondary" v-if="hayDevueltos"
                        @click="ReimpresionDevolucion">Reimprimir Devolucion</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
            usuario: '',
            enviado: false,
            data: [],
            loading: true,
            searchTerm: '',
            selectedState: '', // Nuevo valor para rastrear el estado seleccionado
            detallesPorId: [],
            selectedItem: null,
            check_articulos: [],
            nombre_resguardo: null,
            idResguado: null,
            nombre: '',
            disabled: true,
            baja: false,
            hayDevueltos: false,
        };
    },
    methods: {
        filterByState() {
            this.filteredData = this.data.filter((datos) => {
                const searchValue = this.searchTerm.toLowerCase();
                const stateFilter = this.selectedState ?
                    datos.estado.toString() === this.selectedState :
                    true;

                return stateFilter &&
                    (
                        datos.resguardo.usuario.toLowerCase().includes(searchValue) ||
                        datos.resguardo.departamento.toLowerCase().includes(searchValue) ||
                        datos.articulos.some((art) => art?.modelo?.toLowerCase().includes(searchValue) || art?.marca?.toLowerCase().includes(searchValue) || art.descripcion.toLowerCase().includes(searchValue))

                    );
            });
        },

        refreshData() {
            axios.get('https://sistemas.lineaitalia.net/api/resguardos')
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
                        this.$swal('Error al cargar los resguardos ' + error, '', 'error');
                    }
                });
        },

        vistaDetalles(nombre, detalles, idResguado) {
            this.disabled = true;
            this.hayDevueltos = false;
            this.selectedItem = detalles;
            this.nombre_resguardo = nombre;
            this.idResguado = idResguado;
            for (let i = 0; i < detalles.length; i++) {
                if (detalles[i].devuelto) {
                    this.hayDevueltos = true;
                    break;
                }
            }
            this.check_articulos = [];
            const checkboxes = document.querySelectorAll('.dev-check');
            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i]?.checked) {
                    checkboxes[i].checked = false;
                }
            }
        },
        Reimpresion(resguardo) {
            const data = {
                resguardo: resguardo.id
            }
            axios.post('https://sistemas.lineaitalia.net/api/reimpresion', data, { responseType: 'arraybuffer' }).then(response => {
                const blob = new Blob([response.data], { type: 'application/pdf' });

                // Puedes mostrar el PDF en una ventana o pestaña
                const url = window.URL.createObjectURL(blob);
                this.$swal('Esta es una Reimpresión', 'Se requiere el Resguardo Original', 'warning').then(resp => window.open(url, '_blank'));

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
                    this.$swal('No se ha podido abrir el archivo ' + error.message(), '', 'error');
                }
            });

        },
        ReimpresionDevolucion() {
            axios.post('https://sistemas.lineaitalia.net/api/reimpresion/devolucion', { resguardo: this.idResguado }, { responseType: 'arraybuffer' })
                .then((response) => {
                    const blob = new Blob([response.data], { type: 'application/pdf' });

                    // Puedes mostrar el PDF en una ventana o pestaña
                    const url = window.URL.createObjectURL(blob);
                    window.open(url, '_blank');
                    this.$swal('Se abrirá el archivo en una nueva pestaña', '', 'success');
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
                        this.$swal('No se ha podido abrir el archivo ' + error.message(), '', 'error');
                    }
                });
        },
        toggleSeleccion(index) {
            const item = this.selectedItem[index];
            const checked_articulo = this.check_articulos.find(art => art.id === item.id);
            //busca por id en this.checked_articulos
            const selectedIndex = this.check_articulos.indexOf(checked_articulo);
            if (selectedIndex === -1) {
                // Si el elemento no está en la lista, lo agregamos
                this.check_articulos.push({ id: item.id, condiciones: null, reparacion: false });
            } else {
                // Si el elemento ya está en la lista, lo quitamos
                this.check_articulos.splice(selectedIndex, 1);
                const check_repair = document.getElementById('flexSwitchCheckDefault_' + index);
                check_repair.checked = false;
                this.detallesPorId[selectedIndex] = "";
            }
            console.dir(this.check_articulos);
        },

        handleCheckBox(index) {
            document.querySelectorAll('.dev-check')[index].checked = true;
            this.toggleSeleccion(index);
        },

        handleConditionsChange(id, index) {
            //recibe el id del artículo en cuestión
            let activo = this.check_articulos.find(art => art.id === id);

            if (!activo) {
                this.$swal({
                    title: '¿Agregar ahora?',
                    text: 'Este artículo aún no está marcado para devolución!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.value) {
                        this.handleCheckBox(index);
                        activo = this.check_articulos.find(art => art.id === id);
                        activo.condiciones = this.detallesPorId[index];
                    } else {
                        this.detallesPorId[index] = "";
                        return;
                    }
                });
            } else {
                activo.condiciones = this.detallesPorId[index];
            }

        },
        markForRepair(id, index) {
            let activo = this.check_articulos.find(art => art.id === id);
            if (!activo) {
                this.$swal({
                    title: '¿Agregar ahora?',
                    text: 'Este artículo aún no está marcado para devolución!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.value) {
                        this.handleCheckBox(index);
                        activo = this.check_articulos.find(art => art.id === id);
                        activo.reparacion = !activo.reparacion;
                    } else {
                        return;
                    }
                });
            } else {
                activo.reparacion = !activo.reparacion;
            }

        },
        resetCheckboxes() {
            this.check_articulos = [];
        },
        Devolucion() {
            // console.log("Devolviendo: ", this.check_articulos);
            const returnData = {
                resguardo: this.idResguado,
                articulos: this.check_articulos
            };

            axios.post("https://sistemas.lineaitalia.net/api/devolucion", returnData, { responseType: 'arraybuffer' })
                .then(response => {
                    const blob = new Blob([response.data], { type: 'application/pdf' });

                    // Puedes mostrar el PDF en una ventana o pestaña
                    const url = window.URL.createObjectURL(blob);
                    window.open(url, '_blank');
                    this.refreshData();
                })
                .catch(error => console.log(error));
        }
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
        axios.get('https://sistemas.lineaitalia.net/api/resguardos')
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
                    console.log(error);
                    this.$swal('Error al cargar los resguardos ' + error, '', 'error');
                }
            });
        return this.data;
    },
    computed: {
        filteredData() {
            if (!this.selectedState && !this.searchTerm) {
                return this.data;
            }

            return this.data.filter((datos) => {
                const searchValue = this.searchTerm.toLowerCase();
                const stateFilter = this.selectedState ?
                    datos?.estado?.toString() === this.selectedState :
                    true;
                return stateFilter &&
                    (
                        datos.resguardo.usuario.toLowerCase().includes(searchValue) ||
                        datos.resguardo.departamento.toLowerCase().includes(searchValue) ||
                        datos.articulos.some((art) => art?.modelo?.toLowerCase().includes(searchValue) || art?.marca?.toLowerCase().includes(searchValue) || art.descripcion.toLowerCase().includes(searchValue))

                    );
            });
        },
        hayElementosSeleccionados() {
            return this.check_articulos.length > 0;
        },
    },
};

</script>
  
<style scoped>
.content {
    margin-left: 10vh;
    margin-top: 20px;
}

.input-group {
    margin-bottom: 20px;
    /* Agrega un margen inferior al grupo de entrada para separarlo del contenido */
}

textarea {
    resize: none;
}

#principal {
    position: sticky;
    top: 0;
}

th {
    vertical-align: middle;
}

td {
    vertical-align: middle;
}


.activo {
    background-color: yellow;
    padding: 5px;
    border-radius: 30px;
}

.inactivo {
    background-color: red;
    padding: 5px;
    border-radius: 30px;
}
</style>