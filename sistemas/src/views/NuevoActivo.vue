<script setup>
import Sidebar from '../components/Sidebar.vue';
import router from '@/router';

</script>

<template>
    <div class="main-container">
        <Sidebar />
        <div class="content">
            <div class="container"><br>
                <h2>Registrar Nuevo Activo</h2><br>
                <form @submit.prevent="enviarDatos">
                    <div id="new" class="row">
                        <div id="izq" class="col">
                            <label>Nombre:</label>
                            <input v-model="nombre" class="form-control" type="text" name="nombre"
                                placeholder="Nombre del artículo" required><br>
                            <label>No. de Serie:</label>
                            <input v-model="serie" class="form-control" type="text" name="serie"
                                placeholder="Numero de serie" required><br>
                            <label>Marca:</label>
                            <input v-model="marca" class="form-control" type="text" name="marca" placeholder="Marca"
                                required><br>
                            <h4>Características:</h4>
                            <textarea v-model="caracteristicas" class="form-control" name="caracteristicas"
                                placeholder="Características del artículo" cols="40" rows="5"></textarea><br>
                            <label>Fecha De Compra:</label>
                            <input v-model="compra" class="form-control" type="date" name="compra" required><br>
                            <label>Precio: $</label>
                            <input v-model="precio" class="form-control" type="text" name="precio"
                                placeholder="¿Cuál fue el precio?" required><br>
                        </div>
                        <div id="der" class="col">
                            <label>Descripción:</label>
                            <input v-model="descripcion" class="form-control" type="text" name="descripcion"
                                placeholder="Descripción corta" required><br>
                            <label>Modelo:</label>
                            <input v-model="modelo" class="form-control" type="text" name="modelo"
                                placeholder="Modelo del artículo" required><br>
                            <h4>Agregados:</h4>
                            <textarea v-model="agregado" class="form-control" name="agregado"
                                placeholder="¿Tiene algún agregado?" cols="40" rows="5"></textarea><br>
                            <label>Tipo:</label>
                            <select v-model="tipo" id="input_card" class="form-select">
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
                            <label>Proveedor:</label>
                            <input v-model="proveedor" class="form-control" type="text" name="proveedor"
                                placeholder="¿Quién es el proveedor?" required><br>
                            <label>Garantía:</label>
                            <input v-model="garantia" class="form-control" type="text" name="garantia"
                                placeholder="¿Cuánto tiempo de garantía tiene?" required><br>
                        </div>
                    </div>
                    <button type="submit" id="btn_menu" class="btn btn-primary form-control" :disabled="disabled">
                        <span class="material-symbols-outlined" style="vertical-align: middle;">save</span>Guardar
                    </button>
                    <br>
                </form>
                <br><br>
            </div>
        </div>
    </div>
</template>
  
<script>
import axios from 'axios';

export default {
    data() {
        return {
            nombre: '',
            serie: '',
            marca: '',
            caracteristicas: '',
            compra: '',
            precio: '',
            descripcion: '',
            modelo: '',
            agregado: '',
            proveedor: '',
            garantia: '',
            tipo: 'Selecciona una opción',
            disabled: false,
        };
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
    methods: {
        enviarDatos() {
            this.disabled = true;
            const formData = {
                nombre: this.nombre,
                descripcion: this.descripcion,
                serie: this.serie,
                marca: this.marca,
                modelo: this.modelo,
                caracteristicas: this.caracteristicas,
                agregado: this.agregado,
                compra: this.compra,
                proveedor: this.proveedor,
                precio: this.precio,
                garantia: this.garantia,
                tipo: this.tipo
            };

            axios.post('https://sistemas.lineaitalia.net/api/activo', formData)
                .then(response => {
                    this.nombre = '';
                    this.serie = '';
                    this.marca = '';
                    this.caracteristicas = '';
                    this.compra = '';
                    this.precio = '';
                    this.descripcion = '';
                    this.modelo = '';
                    this.agregado = '';
                    this.proveedor = '';
                    this.garantia = '';
                    this.tipo = 'Selecciona una opción';
                    this.$swal('El activo se guardó exitosamente!', '', 'success');
                    this.disabled = false;
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
                        this.$swal('Error al guardar: ' + error, '', 'error');
                    }
                    this.disabled = false;
                });
        },
    },
};
</script>
  
<style scoped>
.content {
    margin-left: 10vh;
}

textarea {
    resize: none;
}
</style>