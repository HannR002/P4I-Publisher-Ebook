<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index() { return view('admin.payment-methods.index', ['methods' => PaymentMethod::orderBy('sort_order')->get()]); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('qr_image')) $data['qr_image_path'] = $request->file('qr_image')->store('payment-methods', 'public');
        PaymentMethod::create($data);
        return back()->with('success', 'Metode pembayaran ditambahkan.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validated($request);
        if ($request->hasFile('qr_image')) {
            if ($paymentMethod->qr_image_path) Storage::disk('public')->delete($paymentMethod->qr_image_path);
            $data['qr_image_path'] = $request->file('qr_image')->store('payment-methods', 'public');
        }
        $paymentMethod->update($data);
        return back()->with('success', 'Metode pembayaran diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['bank', 'e_wallet', 'qris', 'other'])],
            'name' => ['required', 'max:255'], 'account_name' => ['nullable', 'max:255'],
            'account_number' => ['nullable', 'max:255'], 'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0'],
            'qr_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
