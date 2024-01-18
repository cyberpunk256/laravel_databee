<template>
  <div class="dropzon_wrap">
    <template v-if="origin">
      <template v-if="type == 'video' && origin">
        <ThreeVideoPlayer v-if="origin" :url="origin" class="vp_form_preview" />
        <v-btn color="primary" class="mt-2" @click="onRemoveOrigin">3DMovieを削除</v-btn>
      </template>
      <template v-if="type == 'image' && origin">
        <v-img :width="300" contain :src="get_path_url(origin)"></v-img>
        <v-btn color="primary" class="mt-2" @click="onRemoveOrigin">Still Imageを削除</v-btn>
      </template>
      <template v-if="type == 'panorama' && origin">
        <v-img :width="300" aspect-ratio="16/9" cover :src="get_path_url(origin)"></v-img>
        <v-btn color="primary" class="mt-2" @click="onRemoveOrigin">Panorama Imageを削除</v-btn>
      </template>
      <template v-if="type == 'gpx' && origin">
        <v-list>
          <Link :href="get_path_url(origin)" target="_blank">{{ get_path_url(origin) }}</Link>
        </v-list>
        <v-btn color="primary" class="mt-2" @click="onRemoveOrigin">GPXファイルを削除</v-btn>
      </template>
    </template>
    <template v-else>
      <div ref="dropzone" class="dropzone needsclick">
        <div class="dz-message needsclick">{{ label }}</div>
      </div>
    </template>
    <v-input :error-messages="error"> </v-input>
    <v-dialog v-model="encodeDialog" persistent width="auto">
      <v-card>
        <v-card-text>動画をアップロードした後エンコードしますか？</v-card-text>
        <v-row class="my-0" justify="center">
          <v-col cols="auto">
            <v-btn variant="elevated" color="primary" size="small" @click="onChangeEncoding(1)">同意する</v-btn>
          </v-col>
          <v-col cols="auto">
            <v-btn size="small" @click="onChangeEncoding(0)">同意しない</v-btn>
          </v-col>
        </v-row>
      </v-card>
    </v-dialog> 
    <!-- <v-dialog v-model="encodeDialog" persistent width="auto">
      <v-card>
        <v-card-text>エンコード中・・・</v-card-text>
        <v-divider></v-divider>
        <v-progress-linear
          :model-value="encodePercent"
          color="light-blue"
          height="10"
          striped
          class="mb-2"
        ></v-progress-linear>
        <v-row class="my-0" justify="center">
          <v-col cols="auto">
            <v-btn @click="onStopEncode">キャンセル</v-btn>
          </v-col>
        </v-row>
      </v-card>
    </v-dialog> -->
  </div>
</template>

<script>
import Dropzone from 'dropzone'
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue'
import { Link } from '@inertiajs/vue3'
import ExifReader from 'exifreader'
import { FFmpeg } from '@ffmpeg/ffmpeg'
import { toBlobURL } from '@ffmpeg/util'
const baseURL = 'https://unpkg.com/@ffmpeg/core-mt@0.12.4/dist/esm'

export default {
  components: { ThreeVideoPlayer, Link },
  props: {
    type: {
      type: String, // Add the type definition for the "type" prop
      required: true,
    },
    label: String,
    dir: String,
    error: String,
    origin: String,
  },
  data() {
    return {
      upload_url: '/admin/media/file_upload',
      accepted_files: null,
      file_extension: null,
      file_path: null,
      file_full_name: null,
      file_name: null,
      video_duration: null,
      image_lat: null,
      image_long: null,
      dropzone: null,
      encodeDialog: false,
      encoding: null,
      ffmpegObj: null,
      inputFilePath: 'input.mp4',
      outputFilePath: 'output.mp4',
      // encodeWidth: 2880,
      encodeWidth: 1920,
      encodePercent: 0,
      encodeComplete: false,
    }
  },
  mounted() {
    console.log('this.type', this.type)
    console.log('this.label', this.label)
    console.log('this.error', this.error)
    // console.log('this.origin', this.origin)
    this.init_data()
    this.init_dropzone()
  },
  methods: {
    onRemoveOrigin() {
      const self = this
      this.$emit('removeOrign')
      this.$nextTick(() => {
        console.log('nextTick')
        console.log('self.dropzone', self.dropzone)
        // if(!self.dropzone) {
        console.log('nextTick-dropzone')
        self.init_dropzone()
        // }
      })
    },
    init_data() {
      if (this.type == 'video') {
        this.accepted_files = 'video/*'
      } else if (this.type == 'gpx') {
        this.accepted_files = '.gpx'
      } else {
        // image
        this.accepted_files = 'image/*'
      }
    },
    async init_presigned_upload(params) {
      const self = this
      self.dropzone.options.clickable = false
      try {
        const { data } = await axios.post(`/admin/media/new_presigned_url`, params)
        if (data.success) {
          self.file_path = data.file_path
          self.file_full_name = data.file_full_name
          self.file_name = data.file_name
          self.video_duration = params.video_duration
          self.image_lat = params.image_lat
          self.image_long = params.image_long

          self.dropzone.options.url = data.presigned_url
          if (self.dropzone.files.length > 0) {
            self.$emit('status', true)
            self.dropzone.processQueue()
          }
        } else {
          self.show_toast()
        }
      } catch (e) {
        console.log(e)
        self.show_toast()
      } finally {
        self.dropzone.options.clickable = true
      }
    },
    init_dropzone() {
      const self = this
      if (!this.$refs.dropzone) return
      this.dropzone = new Dropzone(this.$refs.dropzone, {
        url: '/',
        method: 'put',
        thumbnailHeight: 120,
        thumbnailWidth: 120,
        maxFilesize: 1024 * 1024 * 1024 * 10, // 10Gbyte
        maxFiles: 1,
        acceptedFiles: self.accepted_files,
        binaryBody: true,
        addRemoveLinks: true,
        autoProcessQueue: false,
        dictRemoveFile: '削除',
        dictCancelUpload: 'キャンセル',
        headers: {
          'X-CSRF-TOKEN': self.$page.props.csrf_token,
        },
      })

      this.dropzone.on('addedfile', async function (file) {
        if (this.files.length > 1) {
          this.removeFile(this.files[0])
        }
        const extension = file.name.split('.').pop().toLowerCase()
        if (file.type.indexOf('video/') > -1) {
          self.encodeDialog = true
          const video = document.createElement('video')
          video.src = URL.createObjectURL(file)
          video.addEventListener('loadedmetadata', function () {
            self.init_presigned_upload({
              video_duration: video.duration,
              image_lat: null,
              image_long: null,
              extension: extension,
            })
            video.remove()
            URL.revokeObjectURL(video.src)
          })
          video.load()
        } else {
          let geo_data = {
            image_lat: null,
            image_long: null,
          }
          if (file.type.indexOf('image/') > -1) {
            try {
              const tags = await ExifReader.load(file)
              if (tags) {
                geo_data = {
                  image_lat: tags.GPSLatitude.description,
                  image_long: tags.GPSLongitude.description,
                }
              }
            } catch (e) {
              console.log(e)
            }
          }
          self.init_presigned_upload({
            video_duration: null,
            image_lat: geo_data.image_lat,
            image_long: geo_data.image_long,
            extension: extension,
          })
        }
      })

      this.dropzone.on('removedFile', function (file) {
        if (this.files.length == 0) {
          self.encodeComplete = false
          self.$emit('remove')
        }
      })

      this.dropzone.on('success', function (file, response) {
        self.$emit('status', false)
        self.$emit('success', {
          file_path: self.file_path,
          file_full_name: self.file_full_name,
          file_name: self.file_name,
          video_duration: self.video_duration,
          image_lat: self.image_lat,
          image_long: self.image_long,
          encoding: self.encoding,
        })
      })

      this.dropzone.on('error', function (file, errorMessage) {
        self.$emit('status', false)
        self.show_toast()
      })

      this.dropzone.on('sending', function (file, xhr, formData) {
        // xhr.setRequestHeader('Content-Type', file.type);
        // xhr.send(file);
      })

      this.dropzone.on('uploadprogress', function (file, progress, bytesSent) {
        if (file.previewElement) {
          const progress_text = progress.toFixed(2)
          var progressElement = file.previewElement.querySelector('.dz-progress')
          progressElement.innerHTML = `<span class="dz-status" >${progress_text}%</span><span class="dz-upload" data-dz-uploadprogress style="width: ${progress}%;"></span>`
        }
      })
    },
    onChangeEncoding(value) {
      this.encoding = value
      this.encodeDialog = false
    }
    // async transcode(file) {
    //   const self = this
    //   try {
    //     this.loading = true
    //     this.encodeDialog = true
    //     console.log('file', file)
    //     const file_name = file.name
    //     const ffmpegObj = new FFmpeg()
    //     this.ffmpegObj = ffmpegObj
    //     await ffmpegObj.load({
    //       coreURL: await toBlobURL(`${baseURL}/ffmpeg-core.js`, 'text/javascript'),
    //       wasmURL: await toBlobURL(`${baseURL}/ffmpeg-core.wasm`, 'application/wasm'),
    //       workerURL: await toBlobURL(`${baseURL}/ffmpeg-core.worker.js`, 'text/javascript'),
    //     })

    //     await ffmpegObj.writeFile(this.inputFilePath, new Uint8Array(await file.arrayBuffer()))
    //     await this.dropzone.removeAllFiles()
    //     let duration = 0
    //     ffmpegObj.on('log', ({ type, message }) => {})
    //     ffmpegObj.on('progress', ({ progress, time }) => {
    //       self.encodePercent = parseInt(progress * 100)
    //       duration = time
    //     })
    //     await ffmpegObj.exec([
    //       '-i',
    //       this.inputFilePath,
    //       '-vf',
    //       `scale=${this.encodeWidth}:-1`,
    //       '-c:a',
    //       'copy',
    //       '-c:v',
    //       'libx264',
    //       this.outputFilePath,
    //     ])

    //     if (this.ffmpegObj) {
    //       this.encodeComplete = true
    //       const data = await ffmpegObj.readFile(this.outputFilePath)
    //       const blob = new Blob([data], { type: 'video/mp4' })
    //       const new_file = new File([blob], file_name, { type: 'video/mp4' })
    //       this.dropzone.addFile(new_file)
    //       self.init_presigned_upload({
    //         video_duration: duration / 1000000,
    //         image_lat: null,
    //         image_long: null,
    //       })
    //     }
    //   } catch (e) {
    //     console.log(e)
    //   } finally {
    //     this.loading = false
    //     this.encodeDialog = false
    //   }
    // },
    // async onStopEncode() {
    //   console.log('onStopEncode')
    //   this.encodeDialog = false
    //   if (this.ffmpegObj) {
    //     try {
    //       await this.ffmpegObj.deleteFile(this.inputFilePath)
    //     } catch (e) {
    //       console.log(e)
    //     }
    //     try {
    //       await this.ffmpegObj.deleteFile(this.outputFilePath)
    //     } catch (e) {
    //       console.log(e)
    //     }
    //     this.ffmpegObj.terminate()
    //     this.ffmpegObj = null
    //     this.dropzone.removeAllFiles()
    //   }
    // },
  },
}
</script>
