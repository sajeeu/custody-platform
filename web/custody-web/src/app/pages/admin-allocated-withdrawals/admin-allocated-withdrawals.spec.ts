import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminAllocatedWithdrawals } from './admin-allocated-withdrawals';

describe('AdminAllocatedWithdrawals', () => {
  let component: AdminAllocatedWithdrawals;
  let fixture: ComponentFixture<AdminAllocatedWithdrawals>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminAllocatedWithdrawals]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdminAllocatedWithdrawals);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
