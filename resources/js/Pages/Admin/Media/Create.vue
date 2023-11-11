<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import Form from './Form.vue';

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
      class="mb-2"
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
    <Form 
      :tab="tab"
      :method="method"
      :data="data"
      :action="action"
      @preview="onChangeTab"
      ></Form>
  </AdminLayout>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  data() {
    return {
      tab: 'form',
      method: 'post',
      action: '/admin/media',
      data: {
        name: null,
        video: null,
        image: null,
        gpx: null,
        type: 1, // 3d video
        image_lat: null,
        image_long: null,
        origin_video_path: null,
        origin_image_path: null,
        origin_gpx_path: null,
      }
    }
  },
  mounted() {
    this.onInit()
  },
  methods: {
    onInit() {
    },
    onSubmit() {
      form.post('/admin/user', {
        onSuccess: () => {
          router.visit('/admin/user')
        },
      })
    },
    onChangeTab() {
      this.tab = 'map'
    }
  }
}
</script>
