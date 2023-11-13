<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">ユーザー新規登録</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.name" label="名前" variant="underlined" :error-messages="form.errors.name" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.email"
                label="メールアドレス"
                variant="underlined"
                type="email"
                :error-messages="form.errors.email"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.password"
                label="パースワード"
                variant="underlined"
                type="password"
                :error-messages="form.errors.password"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-select
                v-model="form.pref"
                :items="constant.enums.prefs"
                item-title="text"
                item-value="value"
                label="エリア"
                variant="underlined"
                :error-messages="form.errors.pref"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                disabled
                v-model="form.init_lat"
                label="初期ポジション緯度"
                variant="underlined"
                type="text"
                :error-messages="form.errors.init_lat"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                disabled
                v-model="form.init_long"
                label="初期ポジション緯度"
                variant="underlined"
                type="text"
                :error-messages="form.errors.init_long"
              />
            </v-col>
            <v-col cols="12" sm="12">
              <div id="map" class="input_map"></div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link href="/admin/user" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
          <v-btn type="submit" color="primary">登録</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AdminLayout>
</template>

<script>
import L from 'leaflet';
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  props: ['group_options'],
  data() {
    return {
      breadcrumbs: [
        {
          title: 'ユーザー一覧',
          disabled: false,
          href: '/admin/user',
        },
        {
          title: '新規登録',
          disabled: true,
        },
      ],
      form: useForm({
        name: null,
        email: null,
        password: null,
        pref: null,
        init_lat: null,
        init_long: null,
      }),
      group_options: []
    }
  },
  mounted() {
    const self = this
    self.form.init_lat = self.constant.map.init_pos.lat
    self.form.init_long = self.constant.map.init_pos.long
    self.map_default_option = self.constant.map
    
    self.map = L.map('map').setView(self.map_default_option.view,self.map_default_option.zoom);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)
    const pin_marker = L.marker([self.form.init_lat, self.form.init_long], { draggable: true })
      .addTo(self.map);

    // マウススクロールイベントのリスナーを追加
    pin_marker.on('dragstart', function (e) {
    });
    pin_marker.on('dragend', function (e) {
      var newPosition = pin_marker.getLatLng();
      self.form.init_lat = newPosition.lat
      self.form.init_long = newPosition.lng
    });
  },
  methods: {
    submit() {
      this.form.post('/admin/user', {
        onSuccess: () => {
          router.visit('/admin/user')
        },
      })
    }
  },  
}
</script>