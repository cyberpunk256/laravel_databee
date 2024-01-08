<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">動画ファイル、GPXファイルをまとめてアップロード</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-form @submit.prevent="submit">
      <v-btn type="submit" :disabled="loading" color="primary">アップロード</v-btn>
    </v-form>
  </AdminLayout>
</template>

<script>
export default {
  data() {
    return {
      breadcrumbs: [
        {
          title: 'アップロード',
          disabled: true,
        },
      ],
      loading: false
    }
  },
  methods: {
    submit() {
      const self = this
      self.loading = true
      this.$inertia.post(
        '/admin/setting',
        {},
        {
          onSuccess: () => {
            self.show_toast()
          },
          onFinish: () => {
            self.loading = false
          },
        },
      )
    },
  },
}
</script>
