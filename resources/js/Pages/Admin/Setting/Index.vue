<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">設定</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.map_gpx_weight"
                label="Gpxラインのウェイト"
                variant="underlined"
                type="number"
                :error-messages="form.errors.map_gpx_weight"
              />
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.map_default_zoom"
                label="マップデフォルトZoom"
                variant="underlined"
                type="map_default_zoom"
                :error-messages="form.errors.map_default_zoom"
              />
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.s3_upload_folder"
                label="S3アップロードフォルダー"
                variant="underlined"
                type="s3_upload_folder"
                :error-messages="form.errors.s3_upload_folder"
              />
            </v-col>
            <v-col cols="12">
              <p>Media Convert オップション</p>
              <v-row v-for="(item, i) in form.media_conver_options">
                <v-col sm="10" md="5">
                  <v-text-field
                    v-model="form.media_conver_options[i].resolution"
                    label="解像度"
                    variant="underlined"
                    type="number"
                    :error-messages="form.errors[`media_conver_options.${i}.resolution`]"
                  />
                </v-col>
                <v-col sm="10" md="5">
                  <v-text-field
                    v-model="form.media_conver_options[i].bitrate"
                    label="Bitrate"
                    variant="underlined"
                    type="number"
                    :error-messages="form.errors[`media_conver_options.${i}.bitrate`]"
                  />
                </v-col>
                <v-col sm="2" md="1">
                  <v-btn icon="mdi-minus" variant="outlined" size="small" color="primary" @click="onRemove(i)"></v-btn>
                </v-col>
              </v-row>
              <div class="mt-2">
                <v-btn color="primary" size="small" @click="onAdd">追加</v-btn>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn type="submit" color="primary">更新</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AdminLayout>
</template>

<script>
import { useForm } from '@inertiajs/vue3'
export default {
  props: ['settings'],
  data() {
    return {
      breadcrumbs: [
        {
          title: '設定',
          disabled: true,
        },
      ],
      form: useForm({
        map_gpx_weight: null,
        map_default_zoom: null,
        s3_upload_folder: null,
        media_conver_options: [],
      }),
    }
  },
  mounted() {
    const self = this
    self.form.map_gpx_weight = self.settings.map_gpx_weight
    self.form.map_default_zoom = self.settings.map_default_zoom
    self.form.s3_upload_folder = self.settings.s3_upload_folder
    self.form.media_conver_options = JSON.parse(self.settings.media_conver_options)
  },
  methods: {
    submit() {
      const self = this
      this.form.post('/admin/setting', {
        onSuccess: () => {
          self.show_toast()
        },
      })
    },
    onRemove(index) {
      this.form.media_conver_options.splice(index, 1)
    },
    onAdd() {
      this.form.media_conver_options.push({
        resolution: null,
        bitrate: null,
      })
    },
  },
}
</script>
