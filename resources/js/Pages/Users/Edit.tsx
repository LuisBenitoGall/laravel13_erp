import { FormEvent, useMemo, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Checkbox, DatePicker, Field, Input, Switch } from '@/Components/Form';
import RecordEditLayout from '@/Components/Records/RecordEditLayout';
import RecordSummary from '@/Components/Records/RecordSummary';
import UserProfileReadout from '@/Components/Users/UserProfileReadout';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import ContactsEmailsPanel, { type UserEmailItem } from '@/Pages/Users/components/ContactsEmailsPanel';
import { type UserPhoneItem } from '@/Pages/Users/components/ContactsPhonesPanel';
import UserCompaniesPanel from '@/Pages/Users/components/UserCompaniesPanel';
import UserDocsPanel, { type UserDocItem } from '@/Pages/Users/components/UserDocsPanel';
import UserImagesPanel, { type UserImageItem } from '@/Pages/Users/components/UserImagesPanel';

const EDIT_TABS = [
    'profile',
    'password',
    'contacts',
    'images',
    'docs',
    'companies',
] as const;

type EditTabId = (typeof EDIT_TABS)[number];

function tabFromPageUrl(url: string): EditTabId {
    const query = url.includes('?') ? url.slice(url.indexOf('?')) : '';
    const value = new URLSearchParams(query).get('tab');

    return EDIT_TABS.includes(value as EditTabId) ? (value as EditTabId) : 'profile';
}

type Contact = { value: string; label: string | null };

type EditUser = {
    id: number;
    name: string;
    surname: string | null;
    email: string | null;
    nif: string | null;
    birth_date: string | null;
    status: boolean;
    is_public: boolean;
    created_at: string | null;
    updated_at: string | null;
};

type Props = {
    context: 'profile' | 'company';
    user: EditUser;
    company_role: string | null;
    emails: Array<UserEmailItem & Contact>;
    phones: Array<UserPhoneItem & Contact>;
    images: UserImageItem[];
    avatar_url: string | null;
    docs: UserDocItem[];
    can: {
        update_profile: boolean;
        change_password: boolean;
        change_visibility_flags: boolean;
        manage_emails: boolean;
        manage_phones: boolean;
        manage_images: boolean;
        manage_docs: boolean;
        manage_companies: boolean;
    };
};

export default function Edit({
    context,
    user,
    company_role,
    emails,
    phones,
    images,
    avatar_url,
    docs,
    can,
}: Props) {
    const { t, i18n } = useTranslation();
    const page = usePage();
    const urlTab = tabFromPageUrl(page.url);
    const [tab, setTab] = useState<string>(urlTab);
    const [prevPageUrl, setPrevPageUrl] = useState(page.url);

    if (page.url !== prevPageUrl) {
        setPrevPageUrl(page.url);
        setTab(urlTab);
    }

    const basePath =
        context === 'profile' ? '/admin/profile' : `/admin/company-users/${user.id}/edit`;
    const profileUrl =
        context === 'profile' ? '/admin/profile' : `/admin/company-users/${user.id}`;
    const passwordUrl =
        context === 'profile' ? '/admin/profile/password' : `/admin/company-users/${user.id}/password`;
    const backHref = context === 'profile' ? undefined : '/admin/company-users';

    function selectTab(next: string) {
        setTab(next);
        const href = next === 'profile' ? basePath : `${basePath}?tab=${next}`;
        window.history.replaceState(window.history.state, '', href);
    }

    const profileForm = useForm({
        name: user.name,
        surname: user.surname ?? '',
        email: user.email ?? '',
        nif: user.nif ?? '',
        birth_date: user.birth_date,
        status: user.status,
        is_public: user.is_public,
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const summaryFields = useMemo(() => {
        const fields = auditRecordSummaryFields({
            created_at: formatDate(user.created_at, i18n.language) || undefined,
            updated_at: formatDate(user.updated_at, i18n.language) || undefined,
        });

        if (context === 'company' && company_role) {
            fields.push({ labelKey: 'rol', value: company_role });
        }

        return fields;
    }, [company_role, context, i18n.language, user.created_at, user.updated_at]);

    const birthFormatted = formatDate(user.birth_date, i18n.language) || null;

    function submitProfile(e: FormEvent) {
        e.preventDefault();
        profileForm.put(profileUrl, { preserveScroll: true });
    }

    function submitPassword(e: FormEvent) {
        e.preventDefault();
        passwordForm.put(passwordUrl, {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset('current_password', 'password', 'password_confirmation'),
        });
    }

    const tabs = [
        {
            id: 'profile',
            labelKey: 'perfil',
            panel: (
                <div className="grid gap-6 lg:grid-cols-[minmax(240px,280px)_1fr]">
                    <UserProfileReadout
                        user={{
                            name: user.name,
                            surname: user.surname,
                            email: user.email,
                            nif: user.nif,
                            birth_date: user.birth_date,
                            status: user.status,
                            is_public: user.is_public,
                        }}
                        emails={emails}
                        phones={phones}
                        avatarUrl={avatar_url}
                        birthDateFormatted={birthFormatted}
                    />

                    <form
                        onSubmit={submitProfile}
                        className="space-y-5 rounded-xl border border-slate-200 bg-white p-6"
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <Field
                                label={t('nombre')}
                                htmlFor="user-name"
                                required
                                error={profileForm.errors.name}
                            >
                                <Input
                                    id="user-name"
                                    value={profileForm.data.name}
                                    onChange={(e) => profileForm.setData('name', e.target.value)}
                                    maxLength={255}
                                />
                            </Field>
                            <Field
                                label={t('apellidos')}
                                htmlFor="user-surname"
                                error={profileForm.errors.surname}
                            >
                                <Input
                                    id="user-surname"
                                    value={profileForm.data.surname}
                                    onChange={(e) => profileForm.setData('surname', e.target.value)}
                                    maxLength={255}
                                />
                            </Field>
                        </div>

                        <Field
                            label={t('email')}
                            htmlFor="user-email"
                            required
                            error={profileForm.errors.email}
                        >
                            <Input
                                id="user-email"
                                type="email"
                                value={profileForm.data.email}
                                onChange={(e) => profileForm.setData('email', e.target.value)}
                                maxLength={255}
                            />
                        </Field>

                        <div className="grid gap-4 md:grid-cols-2">
                            <Field label={t('nif')} htmlFor="user-nif" error={profileForm.errors.nif}>
                                <Input
                                    id="user-nif"
                                    value={profileForm.data.nif}
                                    onChange={(e) => profileForm.setData('nif', e.target.value)}
                                    maxLength={32}
                                />
                            </Field>
                            <Field
                                label={t('fecha_nacimiento')}
                                htmlFor="user-birth-date"
                                error={profileForm.errors.birth_date}
                            >
                                <DatePicker
                                    id="user-birth-date"
                                    value={profileForm.data.birth_date}
                                    onChange={(value) => profileForm.setData('birth_date', value)}
                                />
                            </Field>
                        </div>

                        <div className="flex flex-row flex-wrap items-center gap-x-8 gap-y-3">
                            <Switch
                                id="user-status"
                                checked={profileForm.data.status}
                                disabled={!can.change_visibility_flags}
                                onCheckedChange={(checked) => profileForm.setData('status', checked)}
                                label={t('activo')}
                            />
                            <Checkbox
                                id="user-is-public"
                                checked={profileForm.data.is_public}
                                disabled={!can.change_visibility_flags}
                                onCheckedChange={(checked) => profileForm.setData('is_public', checked)}
                                label={t('perfil_publico')}
                            />
                        </div>

                        <div className="flex justify-end gap-2">
                            <Button type="submit" disabled={profileForm.processing || !can.update_profile}>
                                {t('guardar')}
                            </Button>
                        </div>
                    </form>
                </div>
            ),
        },
        {
            id: 'password',
            labelKey: 'contrasena',
            visible: can.change_password,
            panel: (
                <form
                    onSubmit={submitPassword}
                    className="mx-auto max-w-lg space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                >
                    <Field
                        label={t('contrasena_actual')}
                        htmlFor="current-password"
                        required
                        error={passwordForm.errors.current_password}
                    >
                        <Input
                            id="current-password"
                            type="password"
                            value={passwordForm.data.current_password}
                            onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                            autoComplete="current-password"
                        />
                    </Field>
                    <Field
                        label={t('contrasena_nueva')}
                        htmlFor="new-password"
                        required
                        error={passwordForm.errors.password}
                    >
                        <Input
                            id="new-password"
                            type="password"
                            value={passwordForm.data.password}
                            onChange={(e) => passwordForm.setData('password', e.target.value)}
                            autoComplete="new-password"
                        />
                    </Field>
                    <Field
                        label={t('confirmar_contrasena')}
                        htmlFor="password-confirmation"
                        required
                        error={passwordForm.errors.password_confirmation}
                    >
                        <Input
                            id="password-confirmation"
                            type="password"
                            value={passwordForm.data.password_confirmation}
                            onChange={(e) =>
                                passwordForm.setData('password_confirmation', e.target.value)
                            }
                            autoComplete="new-password"
                        />
                    </Field>
                    <div className="flex justify-end">
                        <Button type="submit" disabled={passwordForm.processing}>
                            {t('guardar')}
                        </Button>
                    </div>
                </form>
            ),
        },
        {
            id: 'contacts',
            labelKey: 'emails_y_telefonos',
            panel: (
                <ContactsEmailsPanel
                    emails={emails}
                    phones={phones}
                    canManage={can.manage_emails && can.manage_phones}
                    storeUrl={
                        context === 'profile'
                            ? '/admin/profile/emails'
                            : `/admin/company-users/${user.id}/emails`
                    }
                    destroyUrl={(id) =>
                        context === 'profile'
                            ? `/admin/profile/emails/${id}`
                            : `/admin/company-users/${user.id}/emails/${id}`
                    }
                    phonesStoreUrl={
                        context === 'profile'
                            ? '/admin/profile/phones'
                            : `/admin/company-users/${user.id}/phones`
                    }
                    phonesUpdateUrl={(id) =>
                        context === 'profile'
                            ? `/admin/profile/phones/${id}`
                            : `/admin/company-users/${user.id}/phones/${id}`
                    }
                    phonesDestroyUrl={(id) =>
                        context === 'profile'
                            ? `/admin/profile/phones/${id}`
                            : `/admin/company-users/${user.id}/phones/${id}`
                    }
                    phonesMarkPrimaryUrl={(id) =>
                        context === 'profile'
                            ? `/admin/profile/phones/${id}/mark-primary`
                            : `/admin/company-users/${user.id}/phones/${id}/mark-primary`
                    }
                />
            ),
        },
        {
            id: 'images',
            labelKey: 'imagenes',
            panel: (
                <div className="rounded-xl border border-slate-200 bg-white p-5">
                    <UserImagesPanel
                        images={images}
                        canManage={can.manage_images}
                        storeUrl={
                            context === 'profile'
                                ? '/admin/profile/images'
                                : `/admin/company-users/${user.id}/images`
                        }
                        destroyUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/images/${id}`
                                : `/admin/company-users/${user.id}/images/${id}`
                        }
                        markFeaturedUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/images/${id}/mark-featured`
                                : `/admin/company-users/${user.id}/images/${id}/mark-featured`
                        }
                        updatePublicUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/images/${id}/public`
                                : `/admin/company-users/${user.id}/images/${id}/public`
                        }
                    />
                </div>
            ),
        },
        {
            id: 'docs',
            labelKey: 'documentos',
            panel: (
                <div className="rounded-xl border border-slate-200 bg-white p-5">
                    <UserDocsPanel
                        docs={docs}
                        canManage={can.manage_docs}
                        storeUrl={
                            context === 'profile'
                                ? '/admin/profile/docs'
                                : `/admin/company-users/${user.id}/docs`
                        }
                        downloadUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/docs/${id}/download`
                                : `/admin/company-users/${user.id}/docs/${id}/download`
                        }
                        destroyUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/docs/${id}`
                                : `/admin/company-users/${user.id}/docs/${id}`
                        }
                    />
                </div>
            ),
        },
        {
            id: 'companies',
            labelKey: 'empresas',
            panel: (
                <div className="rounded-xl border border-slate-200 bg-white p-5">
                    <UserCompaniesPanel
                        canManage={can.manage_companies}
                        tableEndpoint={
                            context === 'profile'
                                ? '/admin/profile/companies/table'
                                : `/admin/company-users/${user.id}/companies/table`
                        }
                        updateUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/companies/${id}`
                                : `/admin/company-users/${user.id}/companies/${id}`
                        }
                        destroyUrl={(id) =>
                            context === 'profile'
                                ? `/admin/profile/companies/${id}`
                                : `/admin/company-users/${user.id}/companies/${id}`
                        }
                    />
                </div>
            ),
        },
    ];

    return (
        <AppLayout
            breadcrumb={
                context === 'profile'
                    ? {
                          optionKey: 'perfil',
                          suboptionKey: 'dashboard',
                          optionHref: '/admin/profile',
                      }
                    : {
                          optionKey: 'usuarios_empresa',
                          suboptionKey: 'editar_usuario',
                          optionHref: '/admin/company-users',
                      }
            }
            showBack={context === 'company'}
            backHref={backHref}
        >
            <RecordEditLayout
                summary={
                    <RecordSummary
                        titleKey="edicion_usuario"
                        titleParams={{
                            name: [user.name, user.surname].filter(Boolean).join(' '),
                        }}
                        fields={summaryFields}
                    />
                }
                tabs={tabs}
                activeTab={tab}
                onTabChange={selectTab}
            />
        </AppLayout>
    );
}
