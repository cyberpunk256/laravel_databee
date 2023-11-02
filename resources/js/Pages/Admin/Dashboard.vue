<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">ダッシュボード</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-card-text>
        <div class="text-h6 text-medium-emphasis">{{ $page.props.auth.user.name }}さんがログインしました。</div>
      </v-card-text>
    </v-card>
  </AdminLayout>
</template>

<script>
export default {
  name: 'DashboardPage',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'ダッシュボード',
          disabled: true,
        },
      ],
    }
  },
}
</script>
