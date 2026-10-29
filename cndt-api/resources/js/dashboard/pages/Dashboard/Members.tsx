import { Head } from "@inertiajs/react";
import { Layout } from "@/components/Layout";
import { EntityPage } from "@/pages/EntityPage";
import { ENTITIES } from "@/lib/entities";

// Personnel — page Inertia /admin/members
export default function Members() {
  return (
    <Layout>
      <Head title="Personnel" />
      <EntityPage config={ENTITIES.members} />
    </Layout>
  );
}
