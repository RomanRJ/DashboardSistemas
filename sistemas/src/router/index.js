import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Nuevo from '../views/NuevoActivo.vue'
import Servidores from '../views/Servidores.vue'
import Inventario from '../views/Inventario.vue'
import NuevoResguardo from '../views/NuevoResguardo.vue'
import Resguardos from '../views/Resguardos.vue'
import Usuarios from '../views/Usuarios.vue'
import Bitacora from '../views/Bitacora.vue'
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/NuevoActivo',
      name: 'Nuevo Activo',
      component:Nuevo,
    },
    {
      path: '/',
      name: 'Login',
      component: Login,
    },
    {
      path: '/Servidores',
      name: 'Servidores',
      component:Servidores,
    },
    {
      path: '/Inventario',
      name: 'Inventario',
      component: Inventario
    },
    {
      path: '/NuevoResguardo',
      name: 'Nuevo Resguardo',
      component: NuevoResguardo
    },
    {
      path: '/Resguardos',
      name: 'Resguardos',
      component: Resguardos
    },
    {
      path: '/Usuarios',
      name: 'Usuarios',
      component: Usuarios
    },
    {
      path: '/Bitacora',
      name: 'Bitacora',
      component: Bitacora
    }
  ]
})

export default router
