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
    <v-row
      justify="space-between"
      container 
      spacing={24}
      class="mb-5"
    >
      <v-col>
        <h5 class="text-h5 font-weight-bold">メディア新規登録</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
      </v-col>
      <v-spacer></v-spacer>
      <v-col cols="auto">
        <v-btn v-if="tab == 'map'" icon="mdi-arrow-left" @click="tab = 'form'"></v-btn>
      </v-col>
    </v-row>
    <MediaForm :data="data"></MediaForm>
  </AdminLayout>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import Map from '@/Components/Map.vue';
export default {
  components: {
    Map
  },
  data() {
    return {
      tab: 'map',
      form: useForm({
        name: null,
        video: null,
        image: null,
        gpx: null,
        type: 1, // 3d video
        image_lat: null,
        image_long: null,
      }),
      video_url: null,
      image_file: null,
      gpx_file: null,
      modal: false,
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
    onFileUploaded(field, data) {
      this.form[field] = {
        name: data.file_name,
        path: data.file_path,
      }
      if(field == 'video') {
        this.video_url = data.presigned_url
      } else if(field == 'image') {
        this.image_file = data.file
      } else if(field == 'gpx') {
        this.gpx_file = data.file
      }
    },
    onValidate() {
      if(!this.form.name) {
        this.form.errors.name = "メディア名を入力してください。";
      }
      if(!this.form.type) {
        this.form.errors.type = "メディア種別を入力してください。";
      }
      if(this.form.type == 1 && !this.form.video) {
        this.form.errors.video = "3D Movieを入力してください。";
      }
      if(this.form.type == 1 && !this.form.gpx) {
        this.form.errors.gpx = "GPXデータを入力してください。";
      }
      if(this.form.type != 1 && !this.form.image) {
        this.form.errors.image = "画像を入力してください。";
      }
      console.log('errors', this.form.errors);
      if(Object.keys(this.form.errors).length > 0) {
        return false
      } else {
        return true
      }
    },
    onPreview() {
      if(this.onValidate()) {

      }
    }
  },
  computed: {
    items() {
      return [
        {
          type: 'video',
          video_url: this.video_url,
          gpx_file: this.gpx_file
        }
      ]
    }
  }
}
</script>
