import { Head } from "@inertiajs/react";
import { Layout } from "@/components/Layout";
import { EntityPage } from "@/pages/EntityPage";
import { ENTITIES } from "@/lib/entities";

// Publications — page Inertia /admin/publications
export default function Publications() {
  return (
    <Layout>
      <Head title="Publications" />
      <EntityPage config={ENTITIES.publications} />
    </Layout>
  );
}
