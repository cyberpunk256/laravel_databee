<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import S3FileUpload from '@/Components/S3FileUpload.vue'

const breadcrumbs = ref([
  {
    title: 'メディア一覧',
    disabled: false,
    href: '/admin/media',
  },
  {
    title: '新規登録',
    disabled: true,
  },
])
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">メディア新規登録</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card v-if="tab == 'form'">
      <v-card-text>
        <v-text-field v-model="form.name" label="メディア名" variant="underlined" :error-messages="form.errors.name" />
        <v-select
          v-model="form.type"
          :items="enums.media_types"
          item-title="text"
          item-value="value"
          label="メディア種別"
          variant="underlined"
          :error-messages="form.errors.type"
        />
        <S3FileUpload 
          @success="onFileUpload('url')"
          :error="form.errors.url"
          label="メディアファイルをアップロードしてください。"
          folder="media"
          class="mt-2"/>
        <S3FileUpload 
          v-if="form.type == 1"
          @success="onFileUpload('gpx_url')"
          :error="form.errors.gpx_url"
          label="GPXファイルをアップロードしてください。"
          folder="gpx"
          class="mt-2"/>
      </v-card-text>
      <v-divider class="mt-12"></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <Link href="/admin/user" as="div">
          <v-btn text>キャンセル</v-btn>
        </Link>
        <v-btn type="submit" color="primary">プレビュー</v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
    <v-card v-if="tab == 'map'">

    </v-card>
  </AdminLayout>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  props: {
  },
  data() {
    return {
      tab: 'form',
      form: useForm({
        name: null,
        email: null,
        password: null,
        phone: null,
        gender: null,
        address: null,
      })
    }
  },
  mounted() {
  },
  methods: {
    submit() {
      form.post('/admin/user', {
        onSuccess: () => {
          router.visit('/admin/user')
        },
      })
    },
    onFileUpload(field, url) {
      this.form[field] = url
    }
  },
}
</script>
