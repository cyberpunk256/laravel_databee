<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
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
        <v-btn v-if="tab == 'map'" icon="mdi-arrow-left" @click="onBack"></v-btn>
      </v-col>
    </v-row>
    <Form 
      type="new"
      :tab="tab"
      :dir="dir"
      :method="method"
      :data="null"
      :action="action"
      @back="onBack"
      @preview="onChangeTab"
      ></Form>
  </AdminLayout>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  props: ['dir'],
  data() {
    return {
      tab: 'form',
      method: 'post',
      action: '/admin/media'
    }
  },
  mounted() {
  },
  methods: {
    onChangeTab() {
      this.tab = 'map'
    },
    onBack() {
      this.tab = 'form'
    }
  }
}
</script>
