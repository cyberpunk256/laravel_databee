<script setup>
import S3FileUpload from '@/Components/Admin/S3FileUpload.vue'
import Map from '@/Pages/Admin/Media/Parts/Map.vue'
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
            :origin="form.origin_video_path"
            :error="form.errors.video"
            type="video"
            @success="value => onFileUploaded('video', value)"
            @remove="onFileUploaded('video', null)"
            @status="value => onUploadStatus('video', value)"
            label="メディアファイルをアップロードしてください。"
            class="mt-2"
          />
          <S3FileUpload
            :origin="form.origin_gpx_path"
            :error="form.errors.gpx"
            type="gpx"
            @success="value => onFileUploaded('gpx', value)"
            @remove="onFileUploaded('gpx', null)"
            @status="value => onUploadStatus('video', value)"
            label="GPXファイルをアップロードしてください。"
            class="mt-2"
          />
        </template>
        <template v-else>
          <S3FileUpload
            :origin="form.origin_image_path"
            :error="form.errors.media"
            :type="form.type == 2 ? 'image' : 'panorama'"
            @success="value => onFileUploaded('image', value)"
            @remove="onFileUploaded('image', null)"
            @status="value => onUploadStatus('video', value)"
            label="メディアファイルをアップロードしてください。"
            class="mt-2"
          />
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
          <v-btn :disabled="computedPreviewStatus" @click.stop="onPreview" color="primary">プレビュー</v-btn>
        </v-col>
      </v-row>
    </template>
    <template v-else>
      <!-- <Map :type="form.type" :media="computedMedia"></Map> -->
      <Map :type="form.type"></Map>
      <v-divider></v-divider>
      <v-row class="py-4" justify="center">
        <v-col cols="auto">
          <Link href="/admin/media" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
        </v-col>
        <v-col cols="auto">
          <v-btn @click.stop="onSubmit" color="primary">保存する</v-btn>
        </v-col>
      </v-row>
    </template>
  </v-card>
</template>

<script>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
export default {
  watch: {
  },
  props: ['tab', 'method', 'record', 'action'],
  data() {
    return {
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
      }),
      modal: false,
      upload_status: {
        submit: false,
        video: false,
        image: false,
        gpx: false,
      }
    }
  },
  mounted() {
    if(this.record) {
      console.log('record', this.record);
      this.form = useForm({
        name: this.record.name,
        video: null,
        image: null,
        gpx: null,
        type: this.record.type,
        image_lat: this.record.image_lat,
        image_long: this.record.image_long,
      })
      if(this.record.type == 1) { // video 
        this.form.origin_video_path = this.record.media_path
        this.form.origin_gpx_path = this.record.gpx_path
      } else {
        this.form.origin_image_path = this.record.media_path
      }
      console.log('this.form', this.form);
    }
  },
  methods: {
    onFileUploaded(field, data) {
      this.form[field] = data
      console.log('form',this.form)
    },
    onValidate() {
      let errors = {}
      if (!this.form.name) {
        errors.name = 'メディア名は必ず入力してください。'
      }
      if (!this.form.type) {
        errors.type = 'メディア種別は必ず入力してください。'
      }
      if (this.form.type == 1 && !this.form.video) {
        errors.video = '3D Movieファイルは必ず入力してください。'
      }
      if (this.form.type == 1 && !this.form.gpx) {
        errors.gpx = 'GPXファイルは必ず入力してください。'
      }
      if (this.form.type == 2 && !this.form.image) {
        errors.image = 'Still Imageファイルは必ず入力してください。'
      }
      if (this.form.type == 3 && !this.form.image) {
        errors.image = 'Panorama Imageファイルは必ず入力してください。'
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
        [field]: value
      }
      console.log('this.uploadd_status', this.upload_status)
    },
    onPreview() {
      if (this.onValidate()) {
        this.$emit('preview');
      }
    },
    onSubmit() {
      const self = this
      this.form.submit(this.method, this.action, {
        onSuccess: () => {
          router.visit('/admin/media')
        },
        onFinish: () => {
          self.show_toast();
        }
      })
    },
  },
  computed: {
    computedPreviewStatus() {
      if(this.upload_status.video || this.upload_status.image || this.upload_status.gpx || this.upload_status.submit) {
        return true;
      } else {
        return false;
      }
    },
    computedMedia() {
      if(this.form.type == 1) { // video
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
        }
      }
    },
  },
}
</script>