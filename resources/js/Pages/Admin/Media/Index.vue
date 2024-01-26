<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">メディア一覧</h5>
    </div>
    <v-card class="pa-4">
      <v-row>
        <v-col :cols="20">
          <v-text-field
            v-model="search"
            label="Search"
            variant="underlined"
            prepend-inner-icon="mdi-magnify"
            hide-details
            clearable
            single-line
          />
        </v-col>
        <v-col cols="auto">
          <Link href="/admin/media/preview" as="div">
            <v-btn color="green" hide-details>地図表示</v-btn>
          </Link>
        </v-col>
        <v-col cols="auto">
          <Link href="/admin/media/create" as="div">
            <v-btn color="primary" hide-details>新規登録</v-btn>
          </Link>
        </v-col>
      </v-row>
      <v-data-table-server
        v-model="selectedItems"
        :items="data.data"
        :items-length="data.total"
        :headers="headers"
        :search="search"
        class="elevation-0"
        :loading="isLoading"
        @update:options="loadItems"
        show-select
      >
        <template #[`item.admin_name`]="{ item }">
          {{ item.raw.admin.name }}
        </template>
        <template #[`item.updated_at`]="{ item }">
          {{ getYmdHiFromDTS(item.raw.updated_at) }}
        </template>
        <template #[`item.type`]="{ item }">
          {{ getTextOfOption(constant.enums.media_types, item.raw.type) }}
          </template>
        <template #[`item.deleted_at`]="{ item }">
          <v-switch 
            color="primary" 
            inset
            hide-details
            :model-value="item.raw.deleted_at ? 0 : 1" 
            :true-value="1"
            :false-value="0"
            @change="onChangeStatus(item.raw.id)"
          ></v-switch>
        </template>
        <template #[`item.status`]="{ item }">
          {{ getStatusText(item.raw.status) }}
        </template>
        <template #[`item.action`]="{ item }">
          <Link :href="`/admin/media/${item.value}/edit`" as="button">
            <v-btn color="warning" icon="mdi-pencil" size="small" />
          </Link>
        </template>
        <template #bottom="{ item }">
          <v-row>
            <v-col cols="auto">
              <v-btn :disabled="selectedItems.length = 0" @click="onRemoveConfirm" color="red" hide-details>選択したメディを削除</v-btn>
            </v-col>
            <v-spacer></v-spacer>
            <v-col cols="auto">
              <v-data-table-footer>
              </v-data-table-footer>
            </v-col>
          </v-row>
        </template>
      </v-data-table-server>
    </v-card>
    <v-row justify="center">
      <v-dialog v-model="deleteDialog" persistent width="auto">
        <v-card>
          <v-card-text>本当に削除しますか？</v-card-text>
          <v-divider></v-divider>
          <v-row class="my-0" justify="center">
            <v-col cols="auto">
              <v-btn @click="deleteDialog = false">キャンセル</v-btn>
            </v-col>
            <v-col cols="auto">
              <v-btn color="red" :loading="isLoading" text @click="onRemoveSelected">削除</v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-dialog>
    </v-row>
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
        { title: '種別', key: 'type', sortable: false },
        { title: '有効', key: 'deleted_at', sortable: false },
        { title: '状態', key: 'status', sortable: false },
        { title: '投稿日', key: 'updated_at', sortable: false },
        { title: '', key: 'action', sortable: false },
      ],
      breadcrumbs: [
        {
          title: 'メディア一覧',
          disabled: true,
        },
      ],
      isLoading: false,
      search: null,
      selectedItems: [],
      deleteDialog: false,
    }
  },
  mounted() {
  }, 
  methods: {
    loadItems({ page, itemsPerPage, sortBy, search }) {
      this.isLoading = true
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
          this.isLoading = false
        },
      })
    },
    onChangeStatus(id) {
      const self = this
      this.isLoading = true
      this.$inertia.post(`/admin/media/${id}/update_status`, {
        value: 3
      }, {
        onFinish: () => {
          self.isLoading = false
          self.show_toast();
        }
      })
    },
    onRemoveConfirm() {
      this.deleteDialog = true
    },
    onRemoveSelected() {
      this.$inertia.post(`/admin/media/delete_records`, {
        ids: this.selectedItems
      }, {
        onFinish: () => {
          self.isLoading = false
          self.show_toast();
        }
      })
    }
  },
}
</script>
