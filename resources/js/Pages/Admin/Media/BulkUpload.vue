<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">動画ファイル、GPXファイルをまとめてアップロード</h5>
      <p class="text-muted mt-3">AWS S3 該当Bucketの{{ upload_path }}にアップロードします。</p>
    </div>
    <v-form @submit.prevent="submit">
      <v-btn type="submit" :loading="loading" color="primary">アップロード</v-btn>
    </v-form>
    <div v-if="upload_names.length > 0" class="mt-3">
      <p class="text-muted">下のファイルをアップロードしました。</p>
      <ul class="mt-3">
        <li v-for="item in  upload_names">{{ item }}</li>
      </ul>
    </div>
  </AdminLayout>
</template>

<script>
export default {
  props: {
    upload_path: String
  },
  data() {
    return {
      breadcrumbs: [
        {
          title: 'アップロード',
          disabled: true,
        },
      ],
      loading: false,
      upload_names: [],
    }
  },
  methods: {
    submit() {
      const self = this
      self.loading = true
      this.$inertia.post(
        '/admin/bulk_upload',
        {},
        {
          onSuccess: () => {
            self.show_toast()
          },
          onFinish: () => {
            self.upload_names = self.flash.data
            self.loading = false
          },
        },
      )
    },
  },
}
</script>
