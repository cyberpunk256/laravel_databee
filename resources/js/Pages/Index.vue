<script setup>
import MapLayout from '@/Layouts/MapLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <MapLayout>
    <template v-slot:map>
      <div id="map" class="fix_map"></div>
    </template>
    <template v-slot:action>
      <v-btn @click="onShowCaptures" icon="mdi-cart" size="large"></v-btn>
    </template>
    <template v-slot:content>
      <v-dialog v-if="modal_type == 1 && modal_video_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <three-video-player 
            :url="modal_video_url"
            :time="modal_time"
            :nearsetLatLng="onNearestLatLng"
            :id="modal_id"
          />
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 2 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <v-img v-if="modal" max-width="1000" contain :src="modal_image_url"></v-img>
          </div>
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 3 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <panorama v-if="modal"  :url="modal_image_url"></panorama>
          </div>
        </v-card>
      </v-dialog>
      <v-dialog v-model="capture_modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="capture_modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <CaptureList></CaptureList>
          </div>
        </v-card>
      </v-dialog>
    </template>
  </MapLayout>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Panorama.vue';
import CaptureList from '@/Components/CaptureList.vue';

export default {
  components: { ThreeVideoPlayer, Panorama, CaptureList },
  props: ['type', 'records'],
  data() {
    return {
      map: null,
      map_default_option: {
        view: [0,0],
        pin: [0,0],
        scale: 1
      },
      modal: false,
      modal_type: 1, // video
      modal_video_url: null,
      modal_image_url: null,
      modal_points: [],
      modal_time: 0,
      capture_modal: false,
      // map
      bounds_sum: new L.LatLngBounds(),
      loaded_gpxs: 0,
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
          color: 'blue', // Change the line color if needed
          weight: 5, // Set the thickness of the line
          opacity: 0.7,
          lineCap: 'round'
        }
      }
    };
  },
  mounted() {
    const self = this
    const init_pos = this.user.init_lat && this.user.init_long ? 
      [this.user.init_lat, this.user.init_long] :
      self.map_default_option.view

    self.map_default_option = this.constant.map
    self.map = L.map('map').setView(init_pos,self.map_default_option.zoom);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)
    for (let i = 0; i < self.records.length; i++) {
      const record = self.records[i];
      if(record.type == 1) { // video
        const gpx_url = self.get_path_url(record.gpx_path)
        new L.GPX(gpx_url, this.gpxOptions)
          .on('loaded', function(e) {
            self.onGpxLoaded(e, record)
          })
      } else if(record.type == 2 || record.type == 3) {
        const coordinate = (record.image_lat && record.image_long) ? 
          [record.image_lat, record.image_long] : this.map_default_option.pin
        
        const pin_marker = L.marker(coordinate, { draggable: true })
          .addTo(self.map)
          .on('click', function() {
            self.onShowModal(record)
          });
        this.bounds_sum.extend(coordinate)
        this.loaded_gpxs += 1
      }
    }
  },
  watch: {
    loaded_gpxs(new_loaded_gpxs) {
      if(new_loaded_gpxs == this.records.length) {
        // this.map.fitBounds(this.bounds_sum);
      }
    },
  },
  methods: {
    onGpxLoaded(e, record) {
      const self = this
      const gpxLayer = e.target;
      this.bounds_sum.extend(gpxLayer.getBounds());
      gpxLayer.addTo(this.map);
      this.loaded_gpxs += 1
      gpxLayer.on('click', function (event) {
        const layers = gpxLayer.getLayers()
        const latlngs = layers[0].getLatLngs()
        const first_latlng = latlngs[0];
        const first_moment =  moment(first_latlng.meta.time)
        self.modal_points = latlngs.map(latlng => {
          const latlng_moment = moment(latlng.meta.time)
          const latlng_time = latlng_moment.diff(first_moment, 'seconds')
          return {
            latlng: latlng,
            time: latlng_time,
          }
        });
        var target_latlng = event.latlng;
        const time = self.onNearestTime(target_latlng)
        self.onShowModal(record, time)
      });
    },
    onNearestTime(target_latlng) {
      try {
        let nearest_point = this.modal_points[0];
        this.modal_points.forEach(function (point, i) {
          if(target_latlng.distanceTo(point.latlng) < target_latlng.distanceTo(nearest_point.latlng)) {
            nearest_point = point
          }
        });
        return nearest_point.time
      } catch(e) {
        console.log(e)
        return 0
      }
    },
    onNearestLatLng(target_time) {
      try {
        let nearest_point = this.modal_points[0];
        this.modal_points.forEach(function (point, i) {
          if(Math.abs(target_time - point.time) < Math.abs(target_time - nearest_point.time) ) {
            nearest_point = point
          }
        });
        return nearest_point.latlng
      } catch(e) {
        console.log(e)
        return null
      }
    },
    onShowModal(record, time) {
      this.modal_id = record.id
      this.modal_type = record.type
      this.modal_time = time
      if(record.type == 1) {
        this.modal_video_url = record.media_path
        this.modal = true
      } else { // image, panorama
        this.modal_image_url = this.get_path_url(record.media_path)
        this.modal = true
      }
    },
    onShowCaptures() {
      this.capture_modal = true
    },
  },
};
</script>