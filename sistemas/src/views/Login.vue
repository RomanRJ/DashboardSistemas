<template>
  <div id="app">
    <div class="container-xs bg-white p-5 rounded">
      <div class="text-center">
        <img class="logo" src="https://lineaitalia.net/imagenes/logocompletonegro.png" alt="Logo">
        <h1 class="mt-3 mb-4">Portal de Sistemas</h1>
        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label for="username" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="username" v-model="username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" v-model="password" required>
          </div>
          <div>
            <h3 v-if="errorMsg" class="error-msg">Usuario o Contraseña Incorrecto</h3>
          </div>
          <button type="submit" class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
</template>
    
<script>
import router from '../router';
import axios from 'axios';

export default {
  data() {
    return {
      username: '',
      password: '',
      errorMsg: false
    };
  },
  methods: {
    submitForm() {
      const userData = {
        user: this.username,
        password: this.password
      }
      this.errorMsg = false;
      axios.post('https://sistemas.lineaitalia.net/api/login', userData)
        .then(response => {
          if (response.data.access_token) {
            axios.defaults.headers.common['Authorization'] = "Bearer " + response.data.access_token;
            sessionStorage.setItem("token", "Bearer " + response.data.access_token);
            router.push('/NuevoActivo');
          }
        })
        .catch(error => {
          this.errorMsg = true;
          setTimeout(() => {
            this.errorMsg = false;
          }, 5000)
        });
    }
  }
};
</script>
    
<style scoped>
#app {
  background-color: #002A3A;
  margin: 0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo {
  max-width: 350px;
}

.error-msg {
  font-size: 1rem;
  color: #8a2626;
  font-weight: bold;
  margin-bottom: 24px;
  transition: 120ms;
}

button {
  background-color: #002A3A;
}
</style>