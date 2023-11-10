<script setup>
import S3FileUpload from '@/Components/S3FileUpload.vue'
</script>

<template>
  <v-card>
    <template v-if="tab == 'form'">
      <v-card-text>
        <v-text-field v-model="form.name" label="メディア名" variant="underlined" :error-messages="form.errors.name" />
        <v-select
          v-model="form.type"
          :items="enums.media_types"
          item-title="text"
          item-value="value"
          label="メディア種別"
          variant="underlined"
          :error-messages="form.errors.type"
          class="mt-4"
        />
        <template v-if="form.type == 1">
          <S3FileUpload
            :error="form.errors.video"
            @success="onFileUploaded('video')"
            @remove="onFileRemoved('video')"
            label="メディアファイルをアップロードしてください。"
            type="video"
            class="mt-2"
          />
          <S3FileUpload
            :error="form.errors.gpx"
            @success="onFileUploaded('gpx')"
            @remove="onFileRemoved('gpx')"
            label="GPXファイルをアップロードしてください。"
            type="gpx"
            class="mt-2"
          />
        </template>
        <template v-else>
          <S3FileUpload
            type="image"
            :error="form.errors.media"
            @success="onFileUploaded('image')"
            @remove="onFileRemoved('image')"
            label="メディアファイルをアップロードしてください。"
            folder="media"
            class="mt-2"
          />
        </template>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <Link href="/admin/user" as="div">
          <v-btn text>キャンセル</v-btn>
        </Link>
        <v-btn @click.stop="onPreview" color="primary">プレビュー</v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </template>
    <template v-else>
      <Map></Map>
    </template>
  </v-card>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import Map from '@/Components/Map.vue'
export default {
  components: {
    Map,
  },
  data() {
    return {
      tab: 'map',
      form: useForm({
        name: null,
        video: null,
        image: null,
        gpx: null,
        type: 1, // 3d video
        image_lat: null,
        image_long: null,
      }),
      origin_video_path: null,
      origin_image_path: null,
      origin_gpx_path: null,
      modal: false,
    }
  },
  mounted() {
    if(this.data) {
      this.form = useForm({
        name: this.data.name,
        video: null,
        image: null,
        gpx: null,
        type: this.data.type,
        image_lat: this.data.image_lat,
        image_long: this.data.image_long,
      })
      if(this.data.type == 1) { // video 
        this.origin_video_path = this.data.media_path
        this.origin_gpx_path = this.data.gpx_path
      } else {
        this.origin_image_path = this.data.media_path
      }
    }
  },
  methods: {
    submit() {
      form.post('/admin/user', {
        onSuccess: () => {
          router.visit('/admin/user')
        },
      })
    },
    onFileUploaded(field, data) {
      this.form[field] = {
        name: data.file_name,
        path: data.file_path,
      }
    },
    onValidate() {
      if (!this.form.name) {
        this.form.errors.name = 'メディア名を入力してください。'
      }
      if (!this.form.type) {
        this.form.errors.type = 'メディア種別を入力してください。'
      }
      if (this.form.type == 1 && !this.form.video) {
        this.form.errors.video = '3D Movieを入力してください。'
      }
      if (this.form.type == 1 && !this.form.gpx) {
        this.form.errors.gpx = 'GPXデータを入力してください。'
      }
      if (this.form.type != 1 && !this.form.image) {
        this.form.errors.image = '画像を入力してください。'
      }
      console.log('errors', this.form.errors)
      if (Object.keys(this.form.errors).length > 0) {
        return false
      } else {
        return true
      }
    },
    onPreview() {
      if (this.onValidate()) {
      }
    },
  },
  computed: {
    items() {
      return [
        {
          type: 'video',
          video_path: this.video_path,
          gpx_path: this.gpx_path,
        },
      ]
    },
  },
}
</script>