<script setup>
import Sidebar from '../components/Sidebar.vue';
import router from '@/router';

</script>
<template>
    <div class="main-container">
        <Sidebar />
        <div class="content">
            <div class="container"><br>
                <h2>Inventario</h2><br>
                <div class="container">
                    <div class="row">
                        <div class="col-8">
                            <label>&nbsp;</label>
                            <div class="input-group">
                                <div class="btn-baja">
                                    <button class="btn btn-secondary" @click="toggleMultiBaja">{{ multibaja ? "Volver" :
                                        "Baja múltiple" }}</button>
                                    <button v-if="multibaja" class="btn btn-danger" title="jálale >:)"
                                        @click="HandleMultiBaja()">Baja</button>
                                </div>
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
                                <option value="1">Libre</option>
                                <option value="2">Ocupado</option>
                                <option value="3">Despiece</option>
                                <option value="4">Para reparar</option>
                                <option value="0">Inactivo</option>
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
                        <thead class="table-dark">
                            <tr>
                                <th v-if="multibaja">Baja</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Marca</th>
                                <th scope="col">Modelo</th>
                                <th scope="col">Resguardo</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Departamento</th>
                                <th scope="col" title="Ver Más"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(datos, index) in filteredData" :key="index">
                                <td v-if="multibaja" style="text-align: center;">
                                    <input type="checkbox" class="form-check-input" :id="'check_baja' + datos.id"
                                        :disabled="datos.estado != 1" @click="AddMultibaja(datos.id)" />
                                </td>
                                <td style="text-align: center;">
                                    <span class="libre" v-if="datos.estado == 1" alt="libre">&nbsp;</span>
                                    <span class="ocupado" v-else-if="datos.estado == 2" alt="ocupado">&nbsp;</span>
                                    <span class="despiece" v-else-if="datos.estado == 3" alt="despiece">&nbsp;</span>
                                    <span class="reparar" v-else-if="datos.estado == 4" alt="reparar">&nbsp;</span>
                                    <span class="inactivo" v-else-if="datos.estado == 0" alt="inactivo">&nbsp;</span>
                                </td>
                                <td>{{ datos.nombre }}</td>
                                <td>{{ datos.descripcion }}</td>
                                <td>{{ datos.marca }}</td>
                                <td>{{ datos.modelo }}</td>
                                <td>{{ datos.resguardo }}</td>
                                <td>{{ datos.username }}</td>
                                <td>{{ datos.departamento }}</td>
                                <td style="text-align: center;">

                                    <button v-if="!multibaja" class="btn btn-primary form-control" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" @click="vistaDetalles(datos)">Ver Más</button>
                                    <input v-if="multibaja" :id="'input_card' + datos.id" class="form-control file-special"
                                        type="file" name="evidencia" @change="AddImageBaja($event, datos.id)"
                                        :disabled="datos.estado !== 1"><br>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="..." aria-hidden="true"
        v-if="selectedItem !== null">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Detalles de {{ selectedItem.nombre }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="container">
                                <button class="btn btn-secondary"
                                    @click="lista_detalles = true, historial_reparaciones = false, historial_resguardos = false">Detalles</button>&nbsp;
                                <button class="btn btn-secondary"
                                    @click="lista_detalles = false, historial_reparaciones = false, historial_resguardos = true">Historial
                                    de resguardos</button>&nbsp;
                                <button class="btn btn-secondary"
                                    @click="lista_detalles = false, historial_reparaciones = true, historial_resguardos = false">Historial
                                    de reparaciones</button>&nbsp;
                                <button v-if="selectedItem.activo === 0" class="btn btn-danger">Ver Baja</button>
                            </div>
                        </div>
                        <div class="row" v-if="lista_detalles == true">
                            <div class="container text-end">
                                <button class="btn btn-primary btn-sm" v-if="disabled == true && selectedItem.activo == 1"
                                    @click="disabled = false"><span class="material-symbols-outlined">edit</span></button>
                                <button class="btn btn-danger btn-sm" v-else-if="selectedItem.activo == 1"
                                    @click="disabled = true"><span class="material-symbols-outlined">close</span></button>
                            </div>
                            <div class="col">
                                <label>Nombre:</label><input id="input_card" class="form-control" type="text" name="nombre"
                                    placeholder="Nombre del artículo" :value="selectedItem.nombre" :disabled="disabled"
                                    @input="(event) => selectedItem.nombre = event.target.value"><br>
                                <label>No. de serie:</label><input id="input_card" class="form-control" type="text"
                                    name="no_serie" placeholder="Número de serie" :value="selectedItem.no_serie"
                                    @input="(event) => selectedItem.no_serie = event.target.value" :disabled="disabled"><br>
                                <label>Marca:</label><input id="input_card" class="form-control" type="text" name="marca"
                                    placeholder="Marca" :value="selectedItem.marca" :disabled="disabled"
                                    @input="(event) => selectedItem.marca = event.target.value"><br>
                                <label>Modelo:</label><input id="input_card" class="form-control" type="text" name="modelo"
                                    placeholder="Modelo" :value="selectedItem.modelo"
                                    @input="(event) => selectedItem.modelo = event.target.value" :disabled="disabled"><br>
                                <label>Características:</label><textarea name="caracteristicas" class="form-control"
                                    cols="30" rows="4" :disabled="disabled"
                                    @input="(event) => selectedItem.caracteristicas = event.target.value">{{ selectedItem.caracteristicas }}</textarea><br>
                                <label>Fecha de compra:</label><input id="input_card" class="form-control" type="date"
                                    name="fecha_compra" :value="selectedItem.fecha_compra" :disabled="disabled"><br>
                                <label>Precio:</label><input id="input_card" class="form-control" type="text" name="precio"
                                    placeholder="Precio del artículo" :value="selectedItem.precio"
                                    @input="(event) => selectedItem.precio = event.target.value" :disabled="disabled"><br>
                                <label v-if="selectedItem.activo === 0">Fecha de Baja:</label><input id="input_card"
                                    v-if="selectedItem.activo === 0" class="form-control" type="text" name="precio"
                                    placeholder="Precio del artículo" :value="selectedItem.fecha_baja"
                                    @input="(event) => selectedItem.precio = event.target.value" :disabled="disabled"><br>
                            </div>
                            <div class="col">
                                <label>Descripción:</label><input id="input_card" class="form-control" type="text"
                                    name="descripcion" placeholder="Descripción" :value="selectedItem.descripcion"
                                    @input="(event) => selectedItem.descripcion = event.target.value"
                                    :disabled="disabled"><br>
                                <label>Agregados:</label><textarea name="agregados" class="form-control" cols="30" rows="8"
                                    @input="(event) => selectedItem.agregados = event.target.value"
                                    :disabled="disabled">{{ selectedItem.agregados }}</textarea><br>
                                <label>Fecha de registro:</label><input id="input_card" class="form-control" type="date"
                                    name="fecha_registro" :value="selectedItem.fecha_carga" :disabled="disabled"><br>
                                <label>Tipo:</label>
                                <select @change="(event) => selectedItem.tipo = event.target.value"
                                    :value="selectedItem.tipo ? selectedItem.tipo : 'Selecciona una opción'" id="input_card"
                                    :disabled="disabled" class="form-select">
                                    <option>Selecciona una opción</option>
                                    <option>Laptop</option>
                                    <option>CPU</option>
                                    <option>Monitor</option>
                                    <option>Cámara</option>
                                    <option>IMac</option>
                                    <option>Celular</option>
                                    <option>Impresora</option>
                                    <option>VR</option>
                                </select><br>
                                <label>Proveedor:</label><input id="input_card" class="form-control" type="text"
                                    name="proveedor" placeholder="proveedor" :value="selectedItem.proveedor"
                                    @input="(event) => selectedItem.proveedor = event.target.value"
                                    :disabled="disabled"><br>
                                <label>Garantía:</label><input id="input_card" class="form-control" type="text"
                                    name="garantia" placeholder="Tiempo de garantía" :value="selectedItem.garantia"
                                    @input="(event) => selectedItem.garantia = event.target.value" :disabled="disabled"><br>
                                <label v-if="selectedItem.activo === 0">Motivo de Baja:</label><input id="input_card"
                                    v-if="selectedItem.activo === 0" class="form-control" type="text" name="precio"
                                    placeholder="Precio del artículo" :value="selectedItem.motivo_baja"
                                    @input="(event) => selectedItem.precio = event.target.value" :disabled="disabled"><br>
                            </div>
                        </div>
                        <div class="row" v-else-if="historial_resguardos == true">
                            <div class="table-container">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>

                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row" v-else-if="historial_reparaciones == true">
                            historial_reparaciones
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" v-if="selectedItem.activo == 1 && lista_detalles == true"
                        @click="baja = true" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Baja</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" v-if="disabled == false" @click="Cambios">Guardar
                        Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true" v-if="baja == true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Dar de baja {{ selectedItem.nombre }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label>Motivo:</label><input id="input_card" class="form-control" type="text" name="motivo"
                        placeholder="Motivo de baja" v-model="motivo"><br>
                    <label>Imágen de evidencia:</label><input id="input_card" class="form-control" type="file"
                        name="evidencia" @change="handleImageUpload"><br>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault"
                            v-model="isChecked">
                        <label class="form-check-label" for="flexSwitchCheckDefault">{{ isChecked ? 'Baja Definitiva' :
                            'Para Despiece' }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" @click="Baja">Continuar</button>
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
            nombre: '',
            motivo: '',
            evidencia: null,
            disabled: true,
            baja: false,
            isChecked: false,
            lista_detalles: true,
            historial_resguardos: false,
            historial_reparaciones: false,
            multibaja: false,
            ids_baja: []
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
                        datos.nombre.toLowerCase().includes(searchValue) ||
                        datos.descripcion.toLowerCase().includes(searchValue) ||
                        datos.no_serie.toLowerCase().includes(searchValue) ||
                        datos.marca.toLowerCase().includes(searchValue) ||
                        datos.modelo.toLowerCase().includes(searchValue) ||
                        datos.resguardo.toLowerCase().includes(searchValue) ||
                        datos.agregados?.toLowerCase()?.includes(searchValue) ||
                        datos.caracteristicas.toLowerCase().includes(searchValue) ||
                        datos.fecha_carga.toLowerCase().includes(searchValue) ||
                        datos.fecha_compra.toLowerCase().includes(searchValue) ||
                        datos.proveedor.toLowerCase().includes(searchValue) ||
                        datos.precio.toLowerCase().includes(searchValue) ||
                        datos.garantia.toLowerCase().includes(searchValue) ||
                        datos.tipo?.toLowerCase().includes(searchValue)
                    );
            });
        },
        handleImageUpload(e) {
            this.evidencia = e.target.files[0];
        },
        vistaDetalles(detalles) {
            this.disabled = true;
            this.selectedItem = detalles;
        },
        Baja() {
            const formData = new FormData();
            const tipoBaja = this.isChecked ? 0 : 3;
            formData.append('id', this.selectedItem.id);
            formData.append('tipo', tipoBaja);
            formData.append('motivo_baja', this.motivo);
            formData.append('evidencia', this.evidencia);
            axios.post('https://sistemas.lineaitalia.net/api/baja/', formData).then(reponse => {
                this.$swal('El artículo se dio de baja exitosamente!', '', 'success');
                this.RefreshData();
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
                    this.$swal('Error al actualizar activo ' + error, '', 'error');
                }
            })

        },
        RefreshData() {
            axios.get('https://sistemas.lineaitalia.net/api/inventario')
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
                        this.$swal('Error al cargar inventario ' + error, '', 'error');
                    }
                });
        },
        Cambios() {
            //reemplazar url
            axios.put('https://sistemas.lineaitalia.net/api/activo/' + this.selectedItem.id, this.selectedItem).then(data => {
                if (data.data?.error) {
                    this.$swal(data.data?.error, '', 'error');
                } else {
                    this.$swal('Los cambios se guardaron exitosamente!', '', 'success');
                    this.RefreshData();
                }
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
                    style = "text-align: center;"
                }
            });
        },
        toggleMultiBaja() {
            if (this.multibaja) {
                this.ids_baja = [];
                const fileClusters = document.querySelectorAll('.file-recieved');
                fileClusters.forEach(form => form.classList.remove('file-recieved'));
            }
            this.multibaja = !this.multibaja;
        },
        AddImageBaja(event, id) {
            const file = event.target.files[0];
            let art = this.ids_baja.find(arti => arti.id === id);
            if (art) {
                art.image = file;
            } else {
                document.getElementById('check_baja' + id).checked = true;
                this.AddMultibaja(id);
                art = this.ids_baja.find(arti => arti.id === id);
                art.image = file;
            }
            event.target.parentNode.classList.add('file-recieved');
        },
        AddMultibaja(id) {
            const art = this.ids_baja.find(arti => arti.id === id);
            if (art) {
                const index = this.ids_baja.indexOf(art);
                this.ids_baja.splice(index, 1);
            } else {
                // ID doesn't exist, add it
                this.ids_baja.push({ id: id });
            }
            console.log(this.ids_baja);
        },
        validateMultiBaja() {
            for (let i = 0; i < this.ids_baja.length; i++) {
                if (!this.ids_baja[i].image) {
                    return false;
                }
            }
            return true;
        },
        HandleMultiBaja() {
            if (!this.ids_baja.length) {
                this.$swal('No hay artículos Seleccionados!', '', 'error');
                return;
            }
            const formData = new FormData();
            this.ids_baja.forEach(item => {
                formData.append('images[]', item.image);
                formData.append('ids[]', item.id);
            });
            if (!this.validateMultiBaja()) {
                this.$swal('Faltan Imágenes por subir!', '', 'error');
                return;
            }
            axios.post('https://sistemas.lineaitalia.net/api/multibaja', formData, { responseType: 'arraybuffer' }).then((result) => {
                console.log(result.data);
                const blob = new Blob([result.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                this.$swal('Bajas procesadas exitosamente!', 'El pdf se abrirá en una nueva ventana', 'success');
                window.open(url, '_blank')
                this.RefreshData();
                this.toggleMultiBaja();
            }).catch((error) => {
                console.log(error)
                this.$swal('Error al procesar devoluciones ' + error, '', 'error');
            })
        }
    },
    created() {
        //reemplazar url
        try {
            const token = sessionStorage.getItem("token");
            if (token) {
                axios.defaults.headers.common['Authorization'] = token;
            }
        } catch (error) {
            router.push('/');
        }
        axios.get('https://sistemas.lineaitalia.net/api/inventario')
            .then(data => {
                this.data = data.data;
                console.log(this.data);
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
                    this.$swal('Error al cargar inventario ' + error, '', 'error');
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
                    datos.estado.toString() === this.selectedState :
                    true;

                return stateFilter &&
                    (
                        datos.nombre?.toLowerCase().includes(searchValue) ||
                        datos.descripcion?.toLowerCase().includes(searchValue) ||
                        datos.no_serie?.toLowerCase().includes(searchValue) ||
                        datos.marca?.toLowerCase().includes(searchValue) ||
                        datos.modelo?.toLowerCase().includes(searchValue) ||
                        datos.resguardo?.toLowerCase().includes(searchValue) ||
                        datos.agregados?.toLowerCase()?.includes(searchValue) ||
                        datos.caracteristicas?.toLowerCase().includes(searchValue) ||
                        datos.fecha_carga?.toLowerCase().includes(searchValue) ||
                        datos.fecha_compra?.toLowerCase().includes(searchValue) ||
                        datos.proveedor?.toLowerCase().includes(searchValue) ||
                        datos.precio?.toLowerCase().includes(searchValue) ||
                        datos.garantia?.toLowerCase().includes(searchValue) ||
                        datos.tipo?.toLowerCase().includes(searchValue)
                    );
            });
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

.btn-baja {
    width: 160px;
    display: flex;
    justify-content: space-around;
    padding: 0px 4px;
    margin: 0px 4px;
}

textarea {
    resize: none;
}

thead {
    position: sticky;
    top: 0;
}

th {
    vertical-align: middle;
}

td {
    vertical-align: middle;
}

.libre {
    background-color: green;
    padding: 5px;
    border-radius: 30px;
}

.ocupado {
    background-color: yellow;
    padding: 5px;
    border-radius: 30px;
}

.reparar {
    background-color: blue;
    padding: 5px;
    border-radius: 30px;
}

.despiece {
    background-color: orange;
    padding: 5px;
    border-radius: 30px;
}

.inactivo {
    background-color: red;
    padding: 5px;
    border-radius: 30px;
}

.file-special {
    width: 108px;
}

.file-recieved {
    background-color: #0077169e;
}

.btn-secondary {
    z-index: 0 !important;
}
</style>