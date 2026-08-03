<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines - Arabic
    |--------------------------------------------------------------------------
    */

    'accepted'             => 'يجب قبول :attribute.',
    'active_url'           => ':attribute ليس رابطاً صحيحاً.',
    'after'                => 'يجب أن يكون :attribute تاريخاً بعد :date.',
    'alpha'                => 'يجب أن يحتوي :attribute على حروف فقط.',
    'alpha_dash'           => 'يجب أن يحتوي :attribute على حروف وأرقام وشرطات فقط.',
    'alpha_num'            => 'يجب أن يحتوي :attribute على حروف وأرقام فقط.',
    'array'                => 'يجب أن يكون :attribute مصفوفة.',
    'before'               => 'يجب أن يكون :attribute تاريخاً قبل :date.',
    'between'              => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file'    => 'يجب أن يكون حجم ملف :attribute بين :min و :max كيلوبايت.',
        'string'  => 'يجب أن يكون عدد أحرف :attribute بين :min و :max.',
        'array'   => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max.',
    ],
    'boolean'              => 'يجب أن تكون قيمة :attribute صح أو خطأ.',
    'confirmed'            => 'تأكيد :attribute غير مطابق.',
    'date'                 => ':attribute ليس تاريخاً صحيحاً.',
    'date_format'          => 'لا يتطابق :attribute مع الصيغة :format.',
    'different'            => 'يجب أن يختلف :attribute و :other.',
    'digits'               => 'يجب أن يتكون :attribute من :digits رقماً.',
    'digits_between'       => 'يجب أن يتكون :attribute من عدد أرقام بين :min و :max.',
    'email'                => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيحاً.',
    'filled'               => ':attribute مطلوب.',
    'exists'               => 'القيمة المختارة في :attribute غير صحيحة.',
    'image'                => 'يجب أن يكون :attribute صورة.',
    'in'                   => 'القيمة المختارة في :attribute غير صحيحة.',
    'integer'              => 'يجب أن يكون :attribute عدداً صحيحاً.',
    'ip'                   => 'يجب أن يكون :attribute عنوان IP صحيحاً.',
    'max'                  => [
        'numeric' => 'يجب أن تكون قيمة :attribute أصغر من أو تساوي :max.',
        'file'    => 'يجب أن لا يتجاوز حجم ملف :attribute :max كيلوبايت.',
        'string'  => 'يجب أن لا يتجاوز عدد أحرف :attribute :max حرفاً.',
        'array'   => 'يجب أن لا يحتوي :attribute على أكثر من :max عنصر.',
    ],
    'mimes'                => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'min'                  => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من أو تساوي :min.',
        'file'    => 'يجب أن لا يقل حجم ملف :attribute عن :min كيلوبايت.',
        'string'  => 'يجب أن لا يقل عدد أحرف :attribute عن :min حرفاً.',
        'array'   => 'يجب أن يحتوي :attribute على الأقل على :min عنصر.',
    ],
    'not_in'               => 'القيمة المختارة في :attribute غير صحيحة.',
    'numeric'              => 'يجب أن يكون :attribute رقماً.',
    'regex'                => 'صيغة :attribute غير صحيحة.',
    'required'             => ':attribute مطلوب.',
    'same'                 => 'يجب أن يتطابق :attribute مع :other.',
    'size'                 => [
        'numeric' => 'يجب أن تساوي قيمة :attribute :size.',
        'file'    => 'يجب أن يكون حجم ملف :attribute :size كيلوبايت.',
        'string'  => 'يجب أن يتكون :attribute من :size أحرف.',
        'array'   => 'يجب أن يحتوي :attribute على :size عناصر.',
    ],
    'unique'               => 'قيمة :attribute مستخدمة من قبل.',
    'url'                  => 'صيغة :attribute غير صحيحة.',
    'timezone'             => 'يجب أن يكون :attribute منطقة زمنية صحيحة.',

    'attributes' => [
        'national_id' => 'رقم الهوية',
        'password'    => 'كلمة المرور',
        'email'       => 'البريد الإلكتروني',
        'name'        => 'الاسم',
    ],

];
