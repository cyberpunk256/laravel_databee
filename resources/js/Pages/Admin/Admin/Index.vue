<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">管理者一覧</h5>
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
        <Link href="/admin/admin/create" as="div">
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
        <template #[`item.role`]="{ item }">
          {{ getTextOfOption(constant.enums.roles, item.raw.role) }}
          </template>
        <template #[`item.group_name`]="{ item }">
          {{ item.raw.group ? item.raw.group.name : null }}
        </template>
        <template #[`item.pref`]="{ item }">
          {{ getTextOfOption(constant.enums.prefs, item.raw.pref) }}
        </template>
        <template #[`item.action`]="{ item }">
          <Link :href="`/admin/admin/${item.value}/edit`" as="button">
            <v-icon color="warning" icon="mdi-pencil" size="small" />
          </Link>
          <v-icon class="ml-2" color="error" icon="mdi-delete" size="small" @click="deleteItem(item)" />
        </template>
      </v-data-table-server>
    </v-card>
    <v-row justify="center">
      <v-dialog v-model="deleteDialog" persistent width="auto">
        <v-card>
          <v-card-text>本当に削除しますか？</v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn color="error" text @click="deleteDialog = false">キャンセル</v-btn>
            <v-btn color="primary" :loading="isLoading" text @click="submitDelete">削除</v-btn>
          </v-card-actions>
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
        { title: '権限', key: 'role', sortable: false },
        { title: 'グループ', key: 'group_name', sortable: false },
        { title: '名前', key: 'name', sortable: false },
        { title: 'メールアドレス', key: 'email', sortable: false },
        { title: '都道府県', key: 'pref', sortable: false },
        { title: '緯度', key: 'init_lat', sortable: false },
        { title: '緯度', key: 'init_long', sortable: false },
        { title: 'アクション', key: 'action', sortable: false },
      ],
      breadcrumbs: [
        {
          title: 'ユーザー一覧',
          disabled: true,
        },
      ],
      isLoadingTable: false,
      search: null,
      deleteDialog: false,
      isLoading: false,
      deleteId: null,
    }
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
      this.$inertia.get('/admin/admin', params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoadingTable = false
        },
      })
    },
    deleteItem(item) {
      this.deleteId = item.value
      this.deleteDialog = true
    },
    submitDelete() {
      this.isLoading = true
      this.$inertia.delete(`/admin/admin/${this.deleteId}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoading = false
          this.deleteDialog = false
        },
      })
    },
  },
}
</script>
