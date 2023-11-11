<template>
  <div class="dropzon_wrap">
    <template v-if="origin">
      <template v-if="type == 'video' && origin">
        <three-video-player 
          v-if="origin" 
          :url="get_path_url(origin)"
          class="vp_form_preview"
        />
      </template>
      <template v-if="type == 'image' && origin">
        <v-img
          :width="300"
          contain
          :src="get_path_url(origin)"
        ></v-img>
      </template>
      <template v-if="type == 'panorama' && origin">
        <v-img
          :width="300"
          aspect-ratio="16/9"
          cover
          :src="get_path_url(origin)"
        ></v-img>
      </template>
    </template>
    <template v-else>
      <div ref="dropzone" class="dropzone needsclick">
        <div class="dz-message needsclick">{{ label }}</div>
      </div>
      <v-input :error-messages="error">
      </v-input>
    </template>
  </div>
</template>

<script>
import Dropzone from "dropzone";
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Admin/Panorama.vue';


export default {
  watch: {
  },
  props: [ 'type', 'label', 'error', 'origin' ],
  components: { ThreeVideoPlayer, Panorama },
  data() {
    return {
      upload_url: "/admin/media/file_upload",
      accepted_files: null,
      file_extension: null,
      file_path: null,
      file_name: null,
      video_duration: null,
      dropzone: null
    }
  },
  mounted() {
    this.init_data();
    this.init_dropzone();
  },
  methods: {
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
          self.dropzone.options.url = data.presigned_url
          self.video_duration = params.video_duration
          console.log('files', self.dropzone.files)
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
      this.dropzone = new Dropzone(this.$refs.dropzone, {
        url: '/',
        method: 'put',
        thumbnailHeight: 120,
        thumbnailWidth: 120,
        maxFilesize: 1024 * 1024 * 1024 * 10, // 10Gbyte
        maxFiles: 1,
        binaryBody: true,
        addRemoveLinks: true,
        autoProcessQueue: false,
        dictRemoveFile: "削除",
        dictCancelUpload: "キャンセル",
        headers: {
          'X-CSRF-TOKEN': self.$page.props.csrf_token,
        }
      })
      
      this.dropzone.on("addedfile", function(file) {
        console.log('file', file);
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
              video_duration: video.duration
            })
            video.parentNode.removeChild(video);
            URL.revokeObjectURL(video.src);
          });
          video.load();
        } else {
          self.init_presigned_upload({
            extension: extension,
            type: file.type,
            video_duration: null
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
          video_duration: self.video_duration
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