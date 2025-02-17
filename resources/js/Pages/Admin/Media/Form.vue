<script setup>
import S3FileUpload from '@/Components/Admin/S3FileUpload.vue'
import Map from '@/Pages/Admin/Media/Parts/Map.vue'
</script>

<template>
  <div>
    <p class="mb-2">3DMovieファイルをアップロードする時にエラーが発生した場合にはページをリロードしてください。</p>
    <v-card :class="[tab == 'form' ? 'd-block' : 'd-none']">
      <v-card-text>
        <v-text-field v-model="form.name" label="メディア名" variant="underlined" :error-messages="form.errors.name" />
        <v-select
          v-model="form.type"
          :items="constant.enums.media_types"
          :error-messages="form.errors.type"
          item-title="text"
          item-value="value"
          label="メディア種別"
          variant="underlined"
          class="mt-4"
          @change="onTypeChange"
        />
        <template v-if="form.type == 1">
          <S3FileUpload
            :origin="form.origin_video_path"
            :error="form.errors.video"
            type="video"
            label="メディアファイルをアップロードしてください。"
            class="mt-2"
            @success="(value) => onFileUploaded('video', value)"
            @remove="onRemove('video')"
            @removeOrign="onRemove('origin_video_path')"
            @encoding="onChangeEncoding"
            @status="(value) => onUploadStatus('video', value)"
          />
          <S3FileUpload
            :origin="form.origin_gpx_path"
            :error="form.errors.gpx"
            type="gpx"
            label="GPXファイルをアップロードしてください。"
            class="mt-2"
            @success="(value) => onFileUploaded('gpx', value)"
            @remove="onRemove('gpx')"
            @removeOrign="onRemove('origin_gpx_path')"
            @status="(value) => onUploadStatus('gpx', value)"
          />
        </template>
        <template v-if="form.type == 2">
          <S3FileUpload
            :origin="form.origin_image_path"
            :error="form.errors.image"
            type="image"
            label="メディアファイルをアップロードしてください。"
            class="mt-2"
            @success="(value) => onFileUploaded('image', value)"
            @remove="onRemove('image')"
            @removeOrign="onRemove('origin_image_path')"
            @status="(value) => onUploadStatus('image', value)"
          />
        </template>
        <template v-if="form.type == 3">
          <S3FileUpload
            :origin="form.origin_image_path"
            :error="form.errors.panorama"
            type="panorama"
            label="メディアファイルをアップロードしてください。"
            class="mt-2"
            @success="(value) => onFileUploaded('image', value)"
            @remove="onRemove('image')"
            @removeOrign="onRemove('origin_image_path')"
            @status="(value) => onUploadStatus('image', value)"
          />
        </template>
        <template v-if="form.type == 2 || form.type == 3">
          <v-text-field v-model="form.image_lat" label="経度" variant="underlined" />
          <v-text-field v-model="form.image_long" label="緯度" variant="underlined" />
        </template>
      </v-card-text>
      <v-divider></v-divider>
      <v-row class="py-4" justify="center">
        <v-col cols="auto">
          <Link href="/admin/media" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
        </v-col>
        <v-col cols="auto">
          <v-btn :disabled="computedPreviewStatus" color="primary" @click.stop="onPreview">プレビュー</v-btn>
        </v-col>
      </v-row>
    </v-card>
    <v-card :class="[tab == 'form' ? 'd-none' : 'd-block']">
      <Map v-if="computedRecord" :record="computedRecord" @update="onUpdateLatLng"></Map>
      <v-divider></v-divider>
      <v-row class="py-4" justify="center">
        <v-col cols="auto">
          <v-btn text @click="onBack">戻る</v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn :disabled="loading" color="primary" @click.stop="onSubmit">{{ type == 'new' ? '登録' : '更新' }}</v-btn>
        </v-col>
      </v-row>
    </v-card>
  </div>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  props: ['tab', 'type', 'record', 'action'],
  data() {
    return {
      loading: false,
      form: useForm({
        name: null,
        video: null,
        image: null,
        gpx: null,
        type: 1, // 3d video
        image_lat: null,
        image_long: null,
        origin_video_path: null,
        origin_image_path: null,
        origin_gpx_path: null,
        encoding: null
      }),
      modal: false,
      upload_status: {
        submit: false,
        video: false,
        image: false,
        gpx: false,
      },
    }
  },
  computed: {
    computedPreviewStatus() {
      if (this.upload_status.video || this.upload_status.image || this.upload_status.gpx || this.upload_status.submit) {
        return true
      } else {
        return false
      }
    },
    computedRecord() {
      try {
        if (this.form.type == 1) {
          // video
          const video_path = this.form.origin_video_path ? this.form.origin_video_path : this.form.video.file_path
          const gpx_path = this.form.origin_gpx_path ? this.form.origin_gpx_path : this.form.gpx.file_path
          return {
            type: this.form.type,
            video_path: video_path,
            gpx_path: gpx_path,
          }
        } else {
          const image_path = this.form.origin_image_path ? this.form.origin_image_path : this.form.image.file_path
          return {
            type: this.form.type,
            image_path: image_path,
            image_lat: this.form.image_lat,
            image_long: this.form.image_long,
          }
        }
      } catch (e) {
        console.log(e)
        return false
      }
    },
  },
  mounted() {
    if (this.record) {
      this.form = useForm({
        name: this.record.name,
        video: null,
        image: null,
        gpx: null,
        type: this.record.type,
        image_lat: this.record.image_lat,
        image_long: this.record.image_long,
        origin_video_path: null,
        origin_image_path: null,
        origin_gpx_path: null,
        encoding: null,
      })
      if (this.record.type == 1) {
        // video
        this.form.origin_video_path = this.record.media_path
        this.form.origin_gpx_path = this.record.gpx_path
      } else {
        this.form.origin_image_path = this.record.media_path
      }
    }
  },
  methods: {
    onFileUploaded(field, data) {
      this.form[field] = {
        file_path: data.file_path,
        file_name: data.file_name,
        file_full_name: data.file_full_name,
        video_duration: data.video_duration,
      }
      this.form.image_lat = data.image_lat
      this.form.image_long = data.image_long
      this.form.encoding = data.encoding
      console.log('form', this.form)
    },
    onValidate() {
      let errors = {}
      if (!this.form.name) {
        errors.name = 'メディア名は必ず入力してください。'
      }
      if (!this.form.type) {
        errors.type = 'メディア種別は必ず入力してください。'
      }
      if (this.form.type == 1 && !this.form.origin_video_path && !this.form.video) {
        errors.video = '3D Movieファイルは必ず入力してください。'
      }
      if (this.form.type == 1 && !this.form.origin_gpx_path && !this.form.gpx) {
        errors.gpx = 'GPXファイルは必ず入力してください。'
      }
      if (this.form.type == 2 && !this.form.origin_image_path && !this.form.image) {
        errors.image = 'Still Imageファイルは必ず入力してください。'
      }
      if (this.form.type == 3 && !this.form.origin_image_path && !this.form.image) {
        errors.panorama = 'Panorama Imageファイルは必ず入力してください。'
      }
      this.form.errors = errors
      if (Object.keys(errors).length > 0) {
        return false
      } else {
        return true
      }
    },
    onUploadStatus(field, value) {
      this.upload_status = {
        ...this.upload_status,
        [field]: value,
      }
    },
    onPreview() {
      if (this.onValidate()) {
        this.$emit('preview')
      }
    },
    onBack() {
      this.$emit('back')
    },
    onRemove(field) {
      this.form[field] = null
    },
    onTypeChange() {
      this.form.video = null
      this.form.image = null
      this.form.gpx = null
      this.form.image_lat = null
      this.form.image_long = null
    },
    onUpdateLatLng(latLng) {
      this.form.image_lat = latLng.lat
      this.form.image_long = latLng.lng
    },
    onChangeEncoding(value) {
      this.form.encoding = value
    },
    onSubmit() {
      const self = this
      self.loading = true
      const method = this.type == 'new' ? 'post' : 'put'
      const action = this.type == 'new' ? '/admin/media' : `/admin/media/${this.record.id}`
      this.form.submit(method, action, {
        onFinish: () => {
          self.show_toast()
          self.loading = false
        },
      })
    },
  },
}
</script>
