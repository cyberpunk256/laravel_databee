<script setup>
import NavigationMenu from '@/Components/Admin/NavigationMenu.vue'
</script>

<template>
  <v-app class="bg-grey-lighten-4">
    <v-navigation-drawer v-model="drawer" :rail="rail" permanent>
      <v-list>
        <v-list-item
          href="/admin"
          :prepend-avatar="avatar"
          :title="user.name"
          :subtitle="user.email"
          link
        />
      </v-list>
      <v-divider />
      <NavigationMenu />
    </v-navigation-drawer>
    <v-app-bar color="primary">
      <v-app-bar-nav-icon v-if="$vuetify.display.mobile" @click.stop="drawer = !drawer" />
      <v-app-bar-nav-icon v-else @click.stop="rail = !rail" />
    </v-app-bar>
    <v-main>
      <v-container>
        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>

<script>

export default {
  methods: {
  },
  methods: {
  },
  props: {
  },
  data() {
    return {
      drawer: false,
      rail: false,
    }
  },
  computed: {
    avatar() {
      return `https://www.gravatar.com/avatar`
    },
  },
  mounted() {
    this.drawer = !this.$vuetify.display.mobile
  },
}
</script>
