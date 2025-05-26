'use client';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';

export default function ImageGlissante() {
  const [sliderRef] = useKeenSlider({ loop: true });

  return (
    <div ref={sliderRef} className="keen-slider h-[300px] overflow-hidden">
      {[1, 2, 3].map(i => (
        <div key={i} className="keen-slider__slide flex items-center justify-center bg-yellow-100">
          <h2 className="text-xl text-yellow-800 font-bold">Offre spéciale #{i}</h2>
        </div>
      ))}
    </div>
  );
}
