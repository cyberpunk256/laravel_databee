<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">メディア一覧</h5>
    </div>
    <v-card class="pa-4">
      <div class="d-flex flex-wrap align-center">
        <v-text-field
          v-model="search"
          label="Search"
          variant="underlined"
          prepend-inner-icon="mdi-magnify"
          hide-details
          clearable
          single-line
        />
        <v-spacer />
        <Link href="/admin/media/create" as="div">
          <v-btn color="primary">新規登録</v-btn>
        </Link>
      </div>
      <v-data-table-server
        :items="data.data"
        :items-length="data.total"
        :headers="headers"
        :search="search"
        class="elevation-0"
        :loading="isLoadingTable"
        @update:options="loadItems"
      >
        <template #[`item.admin_name`]="{ item }">
          {{ item.raw.admin.name }}
          </template>
        <template #[`item.type`]="{ item }">
          {{ getTextOfOption(type_options, item.columns.type) }}
          </template>
        <template #[`item.status`]="{ item }">
          <v-switch 
            color="primary" 
            inset
            hide-details
            v-model="item.columns.status" 
            :true-value="1"
            :false-value="0"
            @change="onChangeStatus(item.columns.id)"
          ></v-switch>
        </template>
        <template #[`item.action`]="{ item }">
          <Link :href="`/admin/media/${item.value}/edit`" as="button">
            <v-btn color="warning" icon="mdi-pencil" size="small" />
          </Link>
        </template>
      </v-data-table-server>
    </v-card>
  </AdminLayout>
</template>

<script>
export default {
  props: {
    data: {
      type: Object,
    },
  },
  data() {
    return {
      headers: [
        { title: 'ID', key: 'id', sortable: false },
        { title: 'メディア名', key: 'name', sortable: false },
        { title: '投稿者', key: 'admin_name', sortable: false },
        { title: '投稿日', key: 'updated_at', sortable: false },
        { title: '種別', key: 'type', sortable: false },
        { title: '有効', key: 'status', sortable: false },
        { title: '', key: 'action', sortable: false },
      ],
      breadcrumbs: [
        {
          title: 'メディア一覧',
          disabled: true,
        },
      ],
      isLoadingTable: false,
      search: null,
      deleteDialog: false,
      deleteId: null,
      type_options: []
    }
  },
  mounted() {
    this.type_options = this.enums.media_types
    console.log('this.type_options', this.type_options)
  }, 
  methods: {
    loadItems({ page, itemsPerPage, sortBy, search }) {
      this.isLoadingTable = true
      var params = {
        page: page,
        limit: itemsPerPage,
        sort: sortBy[0],
      }
      if (search) {
        params.search = search
      }
      this.$inertia.get('/admin/media', params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoadingTable = false
        },
      })
    },
    onChangeStatus(id) {
      const self = this
      this.isLoadingTable = true
      this.$inertia.post(`/admin/media/${id}/update_status`, {
        value: 3
      }, {
        onFinish: () => {
          self.isLoadingTable = false
          self.show_toast();
        }
      })
    },
  },
}
</script>
