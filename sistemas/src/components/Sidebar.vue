<template>
  <nav class="navbar">
    <div class="container-fluid">
      <a class="navbar-brand">
        <img src="https://lineaitalia.net/imagenes/isotipoblanco.png" alt="Logo" height="50"
          class="d-inline-block align-text-middle" style="margin:0 1vh;">
      </a>
      <a class="navbar-brand text-end align-text-middle" style="color: white;">Sistemas</a>
    </div>
  </nav>
  <div id="sidebar" :class="{ 'scrolled-sidebar': scrolled }">
    <div id="icons" @mouseenter="showLinks" @mouseleave="hideLinks">
      <router-link to="/NuevoActivo"><span class="material-symbols-outlined">barcode_scanner</span></router-link><br>
      <router-link to="/Inventario"><span class="material-symbols-outlined">lists</span></router-link><br>
      <router-link to="/NuevoResguardo"><span class="material-symbols-outlined">edit_note</span></router-link><br>
      <router-link to="/Resguardos"><span class="material-symbols-outlined">patient_list</span></router-link><br>
      <router-link to="/Usuarios"><span class="material-symbols-outlined">person</span></router-link><br>
      <router-link to="/Servidores"><span class="material-symbols-outlined">dns</span></router-link><br>
      <router-link to="/Bitacora"><span class="material-symbols-outlined">rule</span></router-link><br>
      <router-link to="/" @click="logout"><span class="material-symbols-outlined">logout</span></router-link><br>
    </div>
    <div id="links" v-show="isLinksVisible">
      <span class="nav-link">Nuevo Activo</span><br>
      <span class="nav-link">Inventario</span><br>
      <span class="nav-link">Nuevo Resguardo</span><br>
      <span class="nav-link">Resguardos</span><br>
      <span class="nav-link">Usuarios</span><br>
      <span class="nav-link">Servidores</span><br>
      <span class="nav-link">Bitacora</span><br>
      <span class="nav-link">Salir</span><br>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Sidebar',
  data() {
    return {
      isLinksVisible: false,
      scrolled: false // Nueva variable para controlar el scroll
    };
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    showLinks() {
      this.isLinksVisible = true;
    },
    hideLinks() {
      this.isLinksVisible = false;
    },
    handleScroll() {
      if (window.scrollY > 0) {
        this.scrolled = true; // Si hay scroll, actualizar la variable a true
      } else {
        this.scrolled = false; // Si no hay scroll, actualizar la variable a false
      }
    },
    logout() {
      axios.defaults.headers.common['Authorization'] = null;
      sessionStorage.setItem("token", null);
    }
  }
}
</script>

<style>
* {
  font-family: 'Montserrat', sans-serif;
}

nav {
  background-color: #002A3A;
  color: white;
  position: fixed;
  z-index: 1;
}

nav a {
  color: white;
}

#sidebar {
  margin: 0;
  padding: 0;
  display: flex;
  position: fixed;
  z-index: 1;

}

#links {
  overflow: hidden;
  /* Oculta el contenido que excede el tamaño del contenedor */
  transition: height 1s ease;
  /* Transición suave en altura */
  background-color: #383838;
  border-right: solid #002A3A 4px;
  padding-right: 2px;
  margin: 0;
  width: 20vh;
  height: 100vh;
}

#links span {
  text-decoration: none;
  color: white;
  font-weight: bold;
  vertical-align: middle;

}

#icons {
  background-color: #002A3A;
  margin: 0;
  width: 10vh;
  height: 100vh;
}

.scrolled-sidebar {
  /* Agregar estilos cuando hay scroll */
  top: 0;
  left: 0;
  transition: top 1s ease;
}

#icons span {
  color: white;
  font-size: 35px;
  padding: 30px 0px;
  vertical-align: middle;
  margin: 0;
  width: 100%;
  height: auto;
  text-align: center;
}

.nav-link {
  font-size: 30px;
  margin: 25px 0px;
  text-align: center;
  vertical-align: center;
}

#icons span:hover,
#links span:hover {
  background-color: white;
  color: #002A3A;
}
</style>