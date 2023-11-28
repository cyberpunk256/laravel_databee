<template>
  <div class="dropzon_wrap">
    <template v-if="origin">
      <template v-if="type == 'video' && origin">
        <three-video-player 
          v-if="origin" 
          :url="origin"
          class="vp_form_preview"
        />
        <v-btn @click="onRemoveOrigin" color="primary" class="mt-2">3DMovieを削除</v-btn>
      </template>
      <template v-if="type == 'image' && origin">
        <v-img
          :width="300"
          contain
          :src="get_path_url(origin)"
        ></v-img>
        <v-btn @click="onRemoveOrigin" color="primary" class="mt-2">Still Imageを削除</v-btn>
      </template>
      <template v-if="type == 'panorama' && origin">
        <v-img
          :width="300"
          aspect-ratio="16/9"
          cover
          :src="get_path_url(origin)"
        ></v-img>
        <v-btn @click="onRemoveOrigin" color="primary" class="mt-2">Panorama Imageを削除</v-btn>
      </template>
      <template v-if="type == 'gpx' && origin">
        <v-list>
          <Link :href="get_path_url(origin)" target="_blank">{{ origin }}</Link>
        </v-list>
        <v-btn @click="onRemoveOrigin" color="primary" class="mt-2">GPXファイルを削除</v-btn>
      </template>
    </template>
    <template v-else>
      <div ref="dropzone" class="dropzone needsclick">
        <div class="dz-message needsclick">{{ label }}</div>
      </div>
    </template>
    <v-input :error-messages="error">
    </v-input>
  </div>
</template>

<script>
import Dropzone from "dropzone";
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Panorama.vue';
import { Link } from '@inertiajs/vue3'
import ExifReader from 'exifreader';

export default {  
  props: [ 'type', 'label', 'error', 'origin' ],
  components: { ThreeVideoPlayer, Panorama, Link },
  data() {
    return {
      upload_url: "/admin/media/file_upload",
      accepted_files: null,
      file_extension: null,
      file_path: null,
      file_name: null,
      video_duration: null,
      image_lat: null,
      image_long: null,
      dropzone: null
    }
  },
  mounted() {
    console.log('this.type', this.type)
    console.log('this.label', this.label)
    console.log('this.error', this.error)
    // console.log('this.origin', this.origin)
    this.init_data();
    this.init_dropzone();
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
      if(this.type == 'video') {
        this.accepted_files = "video/*"
      } else if(this.type == 'gpx') {
        this.accepted_files = ".gpx"
      } else { // image
        this.accepted_files = "image/*"
      }
    },
    async init_presigned_upload(params) {
      const self = this
      self.dropzone.options.clickable = false;
      try {
        const { data } = await axios.post(`/admin/media/new_presigned_url`, params);
        if(data.success) {
          self.file_path = data.file_path
          self.file_name = data.file_name
          self.video_duration = params.video_duration
          self.image_lat = params.image_lat
          self.image_long = params.image_long

          self.dropzone.options.url = data.presigned_url
          if(self.dropzone.files.length > 0) {
            self.$emit('status', true)
            self.dropzone.processQueue();
          }
        } else {
          self.show_toast()
        }
      } catch(e) {
        console.log(e);
        self.show_toast()
      } finally {
        self.dropzone.options.clickable = true;
      }
    },
    init_dropzone() {
      const self = this
      // console.log('self.origin', self.origin)
      // if(self.origin) return
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
        dictRemoveFile: "削除",
        dictCancelUpload: "キャンセル",
        headers: {
          'X-CSRF-TOKEN': self.$page.props.csrf_token,
        }
      })
      
      this.dropzone.on("addedfile", async function(file) {
        if (this.files.length > 1) {
          this.removeFile(this.files[0]);
        }
        const extension = file.name.split('.').pop().toLowerCase()
        if(file.type.indexOf('video/') > -1) {
          const video = document.createElement('video');
          video.src = URL.createObjectURL(file);

          // Listen for the 'loadedmetadata' event to get the duration
          video.addEventListener('loadedmetadata', function() {
            self.init_presigned_upload({
              extension: extension,
              type: file.type,
              video_duration: video.duration,
              image_lat: null,
              image_long: null,
            })
            video.remove()
            URL.revokeObjectURL(video.src);
          });
          video.load();
        } else {
          let geo_data = {
            image_lat: null,
            image_long: null,
          }
          if(file.type.indexOf('image/') > -1) {
            try {
              const tags = await ExifReader.load(file);
              if(tags) {
                geo_data = {
                  image_lat: tags.GPSLatitude.description,
                  image_long: tags.GPSLongitude.description,
                }
              }
            } catch(e) {
              console.log(e)
            }
          }
          self.init_presigned_upload({
            extension: extension,
            type: file.type,
            video_duration: null,
            image_lat: geo_data.image_lat,
            image_long: geo_data.image_long,
          })
        }
      });

      this.dropzone.on("removedFile", function(file) {
        if (this.files.length == 0) {
          self.$emit("remove")
        }
      });

      this.dropzone.on("success", function (file, response) {
        self.$emit('status', false)
        self.$emit('success', {
          file_path: self.file_path,
          file_name: self.file_name,
          video_duration: self.video_duration,
          image_lat: self.image_lat,
          image_long: self.image_long,
        })
      });

      this.dropzone.on("error", function (file, errorMessage) {
        self.$emit('status', false)
        self.show_toast()
      });

      this.dropzone.on("sending", function (file, xhr, formData) {
        // xhr.setRequestHeader('Content-Type', file.type);
        // xhr.send(file);
      });

      this.dropzone.on("uploadprogress", function(file, progress, bytesSent) {
        if (file.previewElement) {
          const progress_text = progress.toFixed(2);
          var progressElement = file.previewElement.querySelector(".dz-progress");
          progressElement.innerHTML = `<span class="dz-status" >${progress_text}%</span><span class="dz-upload" data-dz-uploadprogress style="width: ${progress}%;"></span>`
        }
      })
    }
  }
};
</script>