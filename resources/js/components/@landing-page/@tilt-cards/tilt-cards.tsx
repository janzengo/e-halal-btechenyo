import React, { useRef } from "react";
import {
  motion,
  useMotionTemplate,
  useMotionValue,
  useSpring,
} from "framer-motion";

const ROTATION_RANGE = 32.5;
const HALF_ROTATION_RANGE = 32.5 / 2;

interface TiltCardProps {
  children: React.ReactNode;
  className?: string;
  dataAos?: string;
  dataAosDelay?: string;
  bgColor?: string;
}

export const TiltCard: React.FC<TiltCardProps> = ({ 
  children, 
  className = "",
  dataAos,
  dataAosDelay,
  bgColor = "bg-gray-100"
}) => {
  const ref = useRef<HTMLDivElement>(null);

  const x = useMotionValue(0);
  const y = useMotionValue(0);

  const xSpring = useSpring(x);
  const ySpring = useSpring(y);

  const transform = useMotionTemplate`rotateX(${xSpring}deg) rotateY(${ySpring}deg)`;

  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!ref.current) return;

    const rect = ref.current.getBoundingClientRect();

    const width = rect.width;
    const height = rect.height;

    const mouseX = (e.clientX - rect.left) * ROTATION_RANGE;
    const mouseY = (e.clientY - rect.top) * ROTATION_RANGE;

    const rX = (mouseY / height - HALF_ROTATION_RANGE) * -1;
    const rY = mouseX / width - HALF_ROTATION_RANGE;

    x.set(rX);
    y.set(rY);
  };

  const handleMouseLeave = () => {
    x.set(0);
    y.set(0);
  };

  return (
    <div
      data-aos={dataAos}
      data-aos-delay={dataAosDelay}
    >
      <motion.div
        ref={ref}
        onMouseMove={handleMouseMove}
        onMouseLeave={handleMouseLeave}
        style={{
          transformStyle: "preserve-3d",
          transform,
        }}
        className="relative h-full w-full"
      >
        {/* Background layer for 3D depth effect */}
        <div
          style={{
            transform: "translateZ(-30px) scale(1.1)",
            transformStyle: "preserve-3d",
          }}
          className={`absolute inset-0 rounded-xl ${bgColor} shadow-xl`}
        />
        
        {/* Main content layer with the colored background */}
        <div
          style={{
            transform: "translateZ(30px)",
            transformStyle: "preserve-3d",
          }}
          className={`relative z-10 h-full w-full ${className}`}
        >
          {children}
        </div>
      </motion.div>
    </div>
  );
};

// Example usage component (keeping for reference)
export const TiltCardExample = () => {
  return (
    <div className="grid w-full place-content-center bg-gradient-to-br from-indigo-500 to-violet-500 px-4 py-12 text-slate-900">
      <TiltCard className="h-96 w-72 rounded-xl bg-gradient-to-br from-indigo-300 to-violet-300">
        <div
          style={{
            transform: "translateZ(75px)",
            transformStyle: "preserve-3d",
          }}
          className="absolute inset-4 grid place-content-center rounded-xl bg-white shadow-lg"
        >
          <p
            style={{
              transform: "translateZ(50px)",
            }}
            className="text-center text-2xl font-bold"
          >
            HOVER ME
          </p>
        </div>
      </TiltCard>
    </div>
  );
};

export default TiltCard;