<template>
  <div ref="vp_wrap" class="vp_wrap">
    <div class="vp_controls">
      <v-row justify="center">
        <v-col cols="auto">
          <v-btn icon="mdi-rewind" @click="onRewind"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn v-if="isPlaying" icon="mdi-stop" @click="onPause"></v-btn>
          <v-btn v-if="!isPlaying" icon="mdi-play" @click="onPlay"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn icon="mdi-fast-forward" @click="onFast"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn icon="mdi-record" @click="onCapture"></v-btn>
        </v-col>
      </v-row>
      <div class="vp_progress mt-2">
        <v-progress-linear v-model="progress" @click="onSeekTo" :height="10"></v-progress-linear>
      </div>
    </div>
  </div>
</template>

<script>
import { Viewer, VideoPanorama } from 'panolens';

export default {
  props: ['url', 'id', 'time', 'nearsetLatLng'],
  data() {
    return {
      viewer: null,
      panorama: null,
      video: null,
      isPlaying: false,
      progress: 0,
      disabled: false,
      step: 2,
      is_capturing: false,
      capture: null,
      video_url: null
    };
  },
  async mounted() {
    try {
      this.video_url = await this.get_video_url(this.url)      
      this.initVideoPlayer();
    } catch(e) {
      console.log(e)
    }
  },
  unmounted() {
    if(self.video) self.video.remove()
    if(self.panorama) self.panorama.dispose()
    if(self.viewer) self.viewer.dispose()
  },
  methods: {
    async initVideoPlayer() {
      const self = this
      // Create a viewer for the panorama
      self.viewer = new Viewer({
        container: self.$refs.vp_wrap,
        controlBar: false
      });

      // Create a VideoPanorama with your 360-degree video
      self.panorama = new VideoPanorama(self.video_url, {
        autoplay: false, // Disable auto-play for custom control handling
      });

      // Add the VideoPanorama to the viewer
      self.viewer.add(self.panorama);
      self.viewer.controls = null;

      // Get a reference to the video element
      self.video = self.panorama.getVideoElement();
      self.video.style.display = 'none';
      self.video.addEventListener('loadedmetadata', function(e) {
        self.video.currentTime = self.time
      });
      self.video.addEventListener('timeupdate', function() {
        // ビデオの現在の再生時間と総時間を取得
        var currentTime = self.video.currentTime;
        var duration = self.video.duration;
        // プログレスバーの幅を更新
        self.progress = (currentTime / duration) * 100;
      });
      self.video.currentTime = self.time
      this.animate()
    },
    animate(){
      const self = this
      requestAnimationFrame(self.animate);
      self.viewer.renderer.domElement.toBlob(function(blob) { self.capture = blob; }, "image/jpg")
    },
    onPause() {
      this.video.pause();
      this.isPlaying = false;
    },
    async onPlay() {
      try {
        await this.video.play();
        this.isPlaying = true
      } catch(e) {
        console.log(e)
      }
    },
    onRewind() {
      this.video.currentTime = this.video.currentTime < this.step ? 0 : this.video.currentTime - this.step;
    },
    onFast() {
      this.video.currentTime = this.video.duration - this.video.currentTime < this.step ? 
        this.video.duration : this.video.currentTime + this.step;
    },
    onSeekTo(event) {
      const boundingRect = event.currentTarget.getBoundingClientRect();
      const clickX = event.clientX - boundingRect.left;
      const fullWidth = boundingRect.width;
      const percent = (clickX / fullWidth) * 100;
      this.progress = percent;
      const time = parseInt((percent / 100) * this.video.duration);
      this.video.currentTime = time;
    },
    async onCapture() {
      const self = this
      if(!this.capture) return
      try {
        self.is_capturing = true
        const formData = new FormData();
        formData.append('file', self.capture, ' capture.jpg');
        const camera = self.viewer.getCamera();
        const rotation_vector = camera.rotation;  // カメラの回転情報を取得
        const rotation = {
          x: rotation_vector.x,
          y: rotation_vector.y,
          z: rotation_vector.z,
        }
        const zoom = camera.zoom;
        let latlng = self.nearsetLatLng(self.video.currentTime)
        latlng = latlng ? { 
          lat: latlng.lat,
          lng: latlng.lng,
        } :  { 
          lat: null,
          lng: null,
        }
        formData.append('media_id', self.id);
        formData.append('playtime', self.video.currentTime.toFixed(2));
        formData.append('rotation', JSON.stringify(rotation));
        formData.append('zoom', zoom.toFixed(2));
        formData.append('lat', latlng.lat);
        formData.append('long', latlng.lng);
        const { data } = await axios.post('/api/capture', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            contentType: false,
            processData: false,
          }
        })
        if(data.success) {
          self.show_toast("success", data.success)
        } else {
          self.show_toast("error", data.error)
        }
      
      } catch(e) {
        console.log(e)
        self.show_toast()
      } finally {
        self.is_capturing  =false
      }
    }
  },
};
</script>
