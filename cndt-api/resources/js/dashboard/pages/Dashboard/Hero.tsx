import { Head } from "@inertiajs/react";
import { Layout } from "@/components/Layout";
import { HeroCarouselPage } from "@/pages/HeroCarouselPage";

// Carrousel de l'accueil — page Inertia /admin/hero
export default function Hero() {
  return (
    <Layout>
      <Head title="Carrousel accueil" />
      <HeroCarouselPage />
    </Layout>
  );
}
