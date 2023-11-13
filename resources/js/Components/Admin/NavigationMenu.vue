<script setup>
import { Link } from '@inertiajs/vue3'
import AdminMenu from '@/Const/AdminMenu'
</script>

<template>
  <v-list nav>
    <!-- List Menu -->
    <template
      v-for="(item, key) in AdminMenu.items" 
    >
      <Link 
        v-if="item.roles.includes(user.role)"
        :key="key" 
        :href="item.to" 
        as="div">
        <v-list-item
          :prepend-icon="item.icon"
          :title="item.title"
          :exact="item.exact"
          link
          :class="{ 'v-list-item--active': $page.url.startsWith(item.to) }"
        />
      </Link>
    </template>
    <!-- ログアウト -->
    <Link href="/admin/logout" method="post" as="div">
      <v-list-item prepend-icon="mdi-exit-to-app" title="ログアウト" link />
    </Link>
  </v-list>
</template>
