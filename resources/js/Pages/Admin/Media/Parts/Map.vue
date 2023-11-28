<template>
  <div>
    <div id="map" class="admin_map"></div>
    <v-dialog v-if="record.type == 1 && modal_video_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
        <three-video-player :url="modal_video_url"/>
      </v-card>
    </v-dialog>
    <v-dialog v-if="record.type == 2 && modal_image_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
        <div class="vp_content">
          <v-img width="auto" max-width="1200" contain :src="modal_image_url"></v-img>
        </div>
      </v-card>
    </v-dialog>
    <v-dialog v-if="record.type == 3 && modal_image_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
        <div class="vp_content">
          <panorama v-if="modal"  :url="modal_image_url"></panorama>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Panorama.vue';

export default {
  components: { ThreeVideoPlayer, Panorama },
  props: ['record'],
  data() {
    return {
      map: null,
      map_default_option: {
        view: [0,0],
        pin: [0,0],
        zoom: 1
      },
      modal: false,
      modal_video_url: null,
      modal_image_url: null,
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: false,
          endIconUrl: false,
          shadowUrl: false,
        },
        gpx_options: {
          joinTrackSegments: false
        }, 
        polyline_options: {
          color: 'red', // Change the line color if needed
          weight: 5, // Set the thickness of the line
          opacity: 0.7,
          lineCap: 'round'
        }
      },
    };
  },
  mounted() {
    const self = this
    self.map_default_option = this.constant.map
    self.map = L.map('map').setView(self.map_default_option.view,self.map_default_option.zoom);

    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)

    if(self.record.type == 1) { // video
      const gpx_url = self.get_path_url(self.record.gpx_path)
      new L.GPX(gpx_url, this.gpxOptions)
        .on('loaded', self.onLoaded)
        .on('click', function() {
            self.onShowModal()
        });
    } else if(self.record.type == 2 || self.record.type == 3) {
      const coordinate = (self.record.image_lat && self.record.image_long) ? 
        [self.record.image_lat, self.record.image_long] : this.map_default_option.pin
      
      const pin_marker = L.marker(coordinate, { draggable: true })
        .addTo(self.map)
        .on('click', function() {
          self.onShowModal()
        });
        
      // マウススクロールイベントのリスナーを追加
      pin_marker.on('dragstart', function (e) {
      });
      pin_marker.on('dragend', function (e) {
        var newPosition = pin_marker.getLatLng();
        self.$emit('update', newPosition)
      });
    }
  },
  methods: {
    onLoaded(e) {
      this.map.fitBounds(e.target.getBounds());
      e.target.addTo(this.map);
    },
    onShowModal() {
      if(this.record.type == 1) {
        this.modal_video_url = this.record.video_path
        this.modal = true
      } else { // image, panorama
        this.modal_image_url = this.get_path_url(this.record.image_path)
        this.modal = true
      }
    },
  },
};
</script>