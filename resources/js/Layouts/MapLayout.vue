<template>
  <v-app class="bg-grey-lighten-4">
    <slot name="map">
    </slot>
    <v-main>
      <v-container>
        <slot name="content">
        </slot>
      </v-container>
    </v-main>
    <div class="fix_action">
      <slot name="action"></slot>
    </div>
  </v-app>
</template>
