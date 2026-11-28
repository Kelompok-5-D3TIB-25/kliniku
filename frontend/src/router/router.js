import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/auth/LoginView.vue'
import RegisterView from '../views/auth/RegisterView.vue'
import OtpView from '../views/auth/OtpView.vue'
import PatientLayout from '../layouts/PatientLayout.vue'
import BerandaView from '../views/pasien/BerandaView.vue'
import BookingView from '../views/pasien/BookingView.vue'
import RiwayatView from '../views/pasien/RiwayatView.vue'
import ProfilView from '../views/pasien/ProfilView.vue'
import DokterDashboardView from '../views/dokter/DokterDashboardView.vue'
import DashboardView from '../views/admin/DashboardView.vue'
import PendaftaranView from '../views/admin/PendaftaranView.vue'
import JadwalKuotaView from '../views/admin/JadwalKuotaView.vue'
import LaporanView from '../views/admin/LaporanView.vue'
import TvDisplayView from '../views/tv/TvDisplayView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login' // Biar pas dibuka pertama langsung ke halaman Login
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView
    },
    {
      path: '/verifikasi-otp',
      name: 'verifikasi-otp',
      component: OtpView
    },
    // Rute Portal Dokter
    {
      path: '/dokter',
      name: 'dokter-dashboard',
      component: DokterDashboardView
    },
    // Rute Portal Admin Loket
    {
      path: '/admin',
      name: 'admin-dashboard',
      component: DashboardView
    },
    {
      path: '/admin/pendaftaran',
      name: 'admin-pendaftaran',
      component: PendaftaranView
    },
    {
      path: '/admin/jadwal',
      name: 'admin-jadwal',
      component: JadwalKuotaView
    },
    {
      path: '/admin/laporan',
      name: 'admin-laporan',
      component: LaporanView
    },
    // Rute Simulasi TV Ruang Tunggu
    {
      path: '/tv',
      name: 'tv-display',
      component: TvDisplayView
    },
    {
      path: '/simulasi-tv',
      redirect: '/tv'
    },
    // Rute Pasien yang dibungkus oleh PatientLayout
    {
      path: '/',
      component: PatientLayout,
      children: [
        {
          path: 'beranda',
          name: 'beranda',
          component: BerandaView
        },
        {
          path: 'booking',
          name: 'booking',
          component: BookingView
        },
        {
          path: 'riwayat',
          name: 'riwayat',
          component: RiwayatView
        },
        {
          path: 'profil',
          name: 'profil',
          component: ProfilView
        }
      ]
    }
  ]
})

export default router
