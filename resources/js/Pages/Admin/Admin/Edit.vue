<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">管理者編集</h5>
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
                v-model="form.role"
                :items="constant.enums.roles"
                item-title="text"
                item-value="value"
                label="権限"
                variant="underlined"
                :error-messages="form.errors.role"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-select
                v-model="form.group_id"
                :items="group_options"
                item-title="name"
                item-value="id"
                label="グループ"
                variant="underlined"
                :error-messages="form.errors.group_id"
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
          <Link href="/admin/admin" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
          <v-btn type="submit" color="primary">更新</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AdminLayout>
</template>

<script>
import L from 'leaflet';
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  props: ['group_options', 'admin'],
  data() {
    return {
      breadcrumbs: [
        {
          title: '管理者一覧',
          disabled: false,
          href: '/admin/admin',
        },
        {
          title: '編集',
          disabled: true,
        },
      ],
      form: useForm({
        id: null,
        group_id: null,
        name: null,
        email: null,
        password: null,
        role: null,
        pref: null,
        init_lat: null,
        init_long: null,
      }),
      group_options: []
    }
  },
  mounted() {
    const self = this
    self.group_options = self.$page.props.group_options
    self.form.id = self.admin.id
    self.form.role = self.admin.role
    self.form.group_id = self.admin.group_id
    self.form.name = self.admin.name
    self.form.email = self.admin.email
    self.form.password = self.admin.password
    self.form.pref = self.admin.pref
    self.form.init_lat = self.admin.init_lat
    self.form.init_long = self.admin.init_long
    
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
      this.form.patch('/admin/admin/' + this.admin.id, {
        onSuccess: () => {
          router.visit('/admin/admin')
        },
      })
    }
  },  
}
</script>