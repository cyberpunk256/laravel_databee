<script setup>
import { Link } from '@inertiajs/vue3'
import AdminMenu from '@/Const/AdminMenu'
</script>

<template>
  <v-list nav>
    <!-- List Menu -->
    <Link v-for="(item, key) in AdminMenu.items" :key="key" :href="item.to" as="div">
      <v-list-item
        :prepend-icon="item.icon"
        :title="item.title"
        :exact="item.exact"
        link
        :class="{ 'v-list-item--active': $page.url.startsWith(item.to) }"
      />
    </Link>
    <!-- ログアウト -->
    <Link href="/admin/logout" method="post" as="div">
      <v-list-item prepend-icon="mdi-exit-to-app" title="ログアウト" link />
    </Link>
  </v-list>
</template>
